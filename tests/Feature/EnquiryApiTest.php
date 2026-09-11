<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourEnquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_submission_creates_enquiry_with_new_status(): void
    {
        $tour = Tour::factory()->create();

        $response = $this->postJson('/api/enquiries', [
            'tour_id' => $tour->id,
            'name' => 'Nguyen Van A',
            'email' => 'a@example.com',
            'phone' => '0901234567',
            'preferred_month' => '2026-11',
            'message' => 'I would like more information.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', TourEnquiry::STATUS_NEW);

        $this->assertDatabaseHas('tour_enquiries', [
            'tour_id' => $tour->id,
            'email' => 'a@example.com',
            'status' => TourEnquiry::STATUS_NEW,
        ]);
    }

    public function test_public_submission_cannot_choose_its_own_status(): void
    {
        $tour = Tour::factory()->create();

        $response = $this->postJson('/api/enquiries', [
            'tour_id' => $tour->id,
            'name' => 'Nguyen Van A',
            'email' => 'a@example.com',
            'status' => TourEnquiry::STATUS_BOOKED,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', TourEnquiry::STATUS_NEW);

        $this->assertDatabaseHas('tour_enquiries', [
            'email' => 'a@example.com',
            'status' => TourEnquiry::STATUS_NEW,
        ]);
    }

    public function test_missing_required_field_or_malformed_email_is_rejected_without_creating_record(): void
    {
        $tour = Tour::factory()->create();

        $this->postJson('/api/enquiries', [
            'tour_id' => $tour->id,
            'email' => 'not-an-email',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);

        $this->assertDatabaseCount('tour_enquiries', 0);
    }

    public function test_non_existing_tour_is_rejected(): void
    {
        $this->postJson('/api/enquiries', [
            'tour_id' => 999999,
            'name' => 'Nguyen Van A',
            'email' => 'a@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tour_id']);

        $this->assertDatabaseCount('tour_enquiries', 0);
    }

    public function test_valid_status_transition_succeeds_and_returns_updated_enquiry(): void
    {
        $enquiry = TourEnquiry::factory()->create([
            'status' => TourEnquiry::STATUS_NEW,
        ]);

        $this->patchJson("/api/enquiries/{$enquiry->id}/status", [
            'status' => TourEnquiry::STATUS_CONTACTED,
        ])
            ->assertOk()
            ->assertJsonPath('id', $enquiry->id)
            ->assertJsonPath('status', TourEnquiry::STATUS_CONTACTED);

        $this->assertDatabaseHas('tour_enquiries', [
            'id' => $enquiry->id,
            'status' => TourEnquiry::STATUS_CONTACTED,
        ]);
    }

    public function test_invalid_status_transition_is_rejected_and_status_is_unchanged(): void
    {
        $enquiry = TourEnquiry::factory()->create([
            'status' => TourEnquiry::STATUS_NEW,
        ]);

        $this->patchJson("/api/enquiries/{$enquiry->id}/status", [
            'status' => TourEnquiry::STATUS_BOOKED,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas('tour_enquiries', [
            'id' => $enquiry->id,
            'status' => TourEnquiry::STATUS_NEW,
        ]);
    }

    public function test_listing_returns_enquiries_with_their_tour_name(): void
    {
        $tour = Tour::factory()->create(['name' => 'Hanoi Discovery']);
        $enquiry = TourEnquiry::factory()->for($tour)->create();

        $this->getJson('/api/enquiries')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $enquiry->id,
                'tour_name' => 'Hanoi Discovery',
                'status' => $enquiry->status,
            ]);
    }

    public function test_listing_paginates_without_skipping_or_repeating_enquiries(): void
    {
        $tour = Tour::factory()->create();
        $enquiries = TourEnquiry::factory()->count(21)->for($tour)->create();

        $first = $this->getJson('/api/enquiries')
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('per_page', 20)
            ->assertJsonPath('prev_page_url', null);

        $this->assertSame($enquiries->reverse()->take(20)->pluck('id')->all(), array_column($first->json('data'), 'id'));
        $this->assertNotNull($first->json('next_page_url'));

        $this->getJson('/api/enquiries?page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $enquiries->first()->id)
            ->assertJsonPath('data.0.tour_name', $tour->name)
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('next_page_url', null);
    }

    public function test_listing_returns_an_empty_page_when_there_are_no_enquiries(): void
    {
        $this->getJson('/api/enquiries')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('next_page_url', null);
    }

    public function test_listing_rejects_invalid_page_numbers(): void
    {
        foreach (['0', '-1', 'abc', '1.5'] as $page) {
            $this->getJson('/api/enquiries?page='.$page)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('page');
        }
    }

    public function test_all_required_status_transitions_are_supported(): void
    {
        $contactedToBooked = TourEnquiry::factory()->create([
            'status' => TourEnquiry::STATUS_CONTACTED,
        ]);
        $this->patchJson("/api/enquiries/{$contactedToBooked->id}/status", [
            'status' => TourEnquiry::STATUS_BOOKED,
        ])->assertOk();

        $contactedToClosed = TourEnquiry::factory()->create([
            'status' => TourEnquiry::STATUS_CONTACTED,
        ]);
        $this->patchJson("/api/enquiries/{$contactedToClosed->id}/status", [
            'status' => TourEnquiry::STATUS_CLOSED,
        ])->assertOk();

        $bookedToClosed = TourEnquiry::factory()->create([
            'status' => TourEnquiry::STATUS_BOOKED,
        ]);
        $this->patchJson("/api/enquiries/{$bookedToClosed->id}/status", [
            'status' => TourEnquiry::STATUS_CLOSED,
        ])->assertOk();
    }
}
