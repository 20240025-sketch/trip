<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ScheduleItem extends Model
{
    protected $fillable = [
        'day_id',
        'time',
        'title',
        'description',
        'location',
        'transport_type',
        'transport_from',
        'transport_to',
        'transport_duration',
        'transport_cost',
        'order',
    ];

    protected $casts = [
        'time' => 'datetime:H:i',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(Day::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('order');
    }
}
