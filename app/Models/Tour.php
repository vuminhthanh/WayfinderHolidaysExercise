<?php

namespace App\Models;

use Database\Factories\TourFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    /** @use HasFactory<TourFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function enquiries(): HasMany
    {
        return $this->hasMany(TourEnquiry::class);
    }
}
