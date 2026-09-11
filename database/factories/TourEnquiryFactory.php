<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourEnquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

class TourEnquiryFactory extends Factory
{
    protected $model = TourEnquiry::class;

    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'preferred_month' => fake()->optional()->randomElement(['2026-10', '2026-11', '2027-01']),
            'message' => fake()->optional()->sentence(),
            'status' => TourEnquiry::STATUS_NEW,
        ];
    }
}
