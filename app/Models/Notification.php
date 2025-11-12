<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user (author) of the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all read records for this notification
     */
    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }

    /**
     * Check if a user has read this notification
     */
    public function isReadBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->reads()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Mark as read by a user
     */
    public function markAsReadBy(User $user): void
    {
        if ($this->isReadBy($user)) {
            return;
        }

        $this->reads()->create([
            'user_id' => $user->id,
            'read_at' => now(),
        ]);
    }
}
