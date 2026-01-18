<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    protected $fillable = [
        'plan_id',
        'name',
        'class_name',
        'contact',
        'avatar',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function roomAssignment(): HasOne
    {
        return $this->hasOne(RoomAssignment::class);
    }

    public function busAssignment(): HasOne
    {
        return $this->hasOne(BusAssignment::class);
    }
}
