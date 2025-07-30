<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TurnitinThreadStatus;
use Illuminate\Database\Eloquent\Model;

final class TurnitinThread extends Model
{
    protected $fillable = [
        'datetime',
        'student_id',
        'lecture_id',
        'status',

        'name',
        'description',

        'file_original_name',
        'file_name',
    ];

    protected $casts = [
        'datetime' => 'datetime:Y-m-d H:i:s',
        'status' => TurnitinThreadStatus::class,
    ];

    public function comments()
    {
        return $this->hasMany(TurnitinThreadComment::class, 'turnitin_thread_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecture_id');
    }
}
