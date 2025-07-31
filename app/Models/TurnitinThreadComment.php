<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class TurnitinThreadComment extends Model
{
    protected $fillable = [
        'turnitin_thread_id',
        'user_id',
        'comment',
        'file',
        'is_solution',
    ];

    protected $casts = [
        'is_solution' => 'boolean',
    ];

    public function thread()
    {
        return $this->belongsTo(TurnitinThread::class, 'turnitin_thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
