<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'cover_image',
        'is_public',
        'slug',
        'memo',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_public' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->title) . '-' . Str::random(8);
            }
        });
    }

    public function days(): HasMany
    {
        return $this->hasMany(Day::class)->orderBy('day_number');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('order');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('order');
    }
}
