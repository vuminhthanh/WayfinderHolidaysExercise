<?php

namespace App\Models;

use Database\Factories\TourEnquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourEnquiry extends Model
{
    /** @use HasFactory<TourEnquiryFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_BOOKED,
        self::STATUS_CLOSED,
    ];

    private const ALLOWED_TRANSITIONS = [
        self::STATUS_NEW => [self::STATUS_CONTACTED],
        self::STATUS_CONTACTED => [self::STATUS_BOOKED, self::STATUS_CLOSED],
        self::STATUS_BOOKED => [self::STATUS_CLOSED],
        self::STATUS_CLOSED => [],
    ];

    protected $fillable = [
        'tour_id',
        'name',
        'email',
        'phone',
        'preferred_month',
        'message',
        'status',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function canTransitionTo(string $nextStatus): bool
    {
        return in_array(
            $nextStatus,
            self::ALLOWED_TRANSITIONS[$this->status] ?? [],
            true,
        );
    }
}
