<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Folder extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'is_private',
        'order',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'folder_plan')
            ->withTimestamps();
    }
}
