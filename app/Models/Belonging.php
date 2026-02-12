<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Belonging extends Model
{
    protected $fillable = [
        'plan_id',
        'name',
        'type',
        'is_checked',
        'order',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * The users who have checked this belonging.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'belonging_user')
            ->withPivot('is_checked')
            ->withTimestamps();
    }
}
