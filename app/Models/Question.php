<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'user_id',
        'content',
        'is_anonymous',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    protected $appends = ['author_name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('created_at', 'asc');
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return '匿名';
        }
        return $this->user ? $this->user->name : '削除されたユーザー';
    }
}
