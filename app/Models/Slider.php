<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Slider extends Model
{
    protected $fillable = [
        'name',
        'description',
        'photo',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
