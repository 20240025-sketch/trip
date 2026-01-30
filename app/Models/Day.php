<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Day extends Model
{
    protected $fillable = [
        'plan_id',
        'date',
        'day_number',
        'title',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(ScheduleItem::class)->orderBy('time')->orderBy('order');
    }

    /**
     * Get schedule items visible to a specific user
     * Shows: original schedule items (user_id = null) + user's own items + all items for admins
     */
    public function scheduleItemsForUser($user = null): HasMany
    {
        $query = $this->hasMany(ScheduleItem::class);
        
        if (!$user) {
            // No user - only show original schedule items
            return $query->whereNull('user_id')->orderBy('time')->orderBy('order');
        }
        
        if ($user->isAdmin()) {
            // Admin - show all schedule items
            return $query->orderBy('time')->orderBy('order');
        }
        
        // Regular user - show original items + their own items
        return $query->where(function ($q) use ($user) {
            $q->whereNull('user_id')
              ->orWhere('user_id', $user->id);
        })->orderBy('time')->orderBy('order');
    }

    public function roomAssignments(): HasMany
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function busAssignments(): HasMany
    {
        return $this->hasMany(BusAssignment::class);
    }
}
