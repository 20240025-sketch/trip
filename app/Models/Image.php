<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'filename',
        'original_name',
        'path',
        'thumbnail_path',
        'size',
        'mime_type',
        'caption',
        'order',
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
