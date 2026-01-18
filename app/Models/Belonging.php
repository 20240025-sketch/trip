<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
