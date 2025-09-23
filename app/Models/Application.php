<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'institution_id',
        'period_id',
        'status',
        'student_access',
        'submitted_at',
        'notes',
    ];

    protected $casts = [
        'student_access' => 'boolean',
        'submitted_at' => 'datetime',
    ];
}
