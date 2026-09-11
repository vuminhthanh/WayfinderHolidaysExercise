<?php

namespace App\Services;

use App\Models\TourEnquiry;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnquiryService
{
    public function paginate(int $page = 1): Paginator
    {
        return TourEnquiry::query()
            ->with('tour:id,name')
            ->orderByDesc('id')
            ->simplePaginate(20, ['*'], 'page', $page)
            ->through(fn (TourEnquiry $enquiry) => [
                'id' => $enquiry->id,
                'name' => $enquiry->name,
                'email' => $enquiry->email,
                'tour_name' => $enquiry->tour->name,
                'status' => $enquiry->status,
            ]);
    }

    public function create(array $data): TourEnquiry
    {
        $data['status'] = TourEnquiry::STATUS_NEW;

        return TourEnquiry::create($data);
    }

    public function updateStatus(TourEnquiry $enquiry, string $nextStatus): TourEnquiry
    {
        return DB::transaction(function () use ($enquiry, $nextStatus) {
            $current = TourEnquiry::query()
                ->lockForUpdate()
                ->findOrFail($enquiry->id);

            if (! $current->canTransitionTo($nextStatus)) {
                throw ValidationException::withMessages([
                    'status' => [sprintf(
                        'Không thể chuyển trạng thái từ "%s" sang "%s".',
                        $current->status,
                        $nextStatus,
                    )],
                ]);
            }

            $current->update(['status' => $nextStatus]);

            return $current->refresh();
        });
    }
}
