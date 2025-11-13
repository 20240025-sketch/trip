<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $fillable = [
        'user_id',
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

    public function attachments(): HasMany
    {
        return $this->hasMany(PlanAttachment::class)->orderBy('order');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user can view this plan
     */
    public function canView(?User $user): bool
    {
        // Public plans can be viewed by anyone
        if ($this->is_public) {
            return true;
        }

        // Not authenticated users cannot view private plans
        if (!$user) {
            return false;
        }

        // Admins can view all plans
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can view their own plan
        if ($this->user_id === $user->id) {
            return true;
        }

        // Regular users can view plans from same team
        if ($user->isRegularUser() && $this->user) {
            return $user->isSameTeam($this->user);
        }

        return false;
    }

    /**
     * Check if a user can edit this plan
     */
    public function canEdit(?User $user): bool
    {
        // Not authenticated users cannot edit
        if (!$user) {
            return false;
        }

        // Admins can edit all plans
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can edit their own plan
        if ($this->user_id === $user->id) {
            return true;
        }

        // Regular users can edit plans from same team
        if ($user->isRegularUser() && $this->user) {
            return $user->isSameTeam($this->user);
        }

        return false;
    }

    /**
     * Check if a user can delete this plan
     */
    public function canDelete(?User $user): bool
    {
        // Same as edit permissions
        return $this->canEdit($user);
    }
}
