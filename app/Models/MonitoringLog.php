<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'internship_id',
        'supervisor_id',
        'log_date',
        'score',
        'title',
        'content',
        'type',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];
}
