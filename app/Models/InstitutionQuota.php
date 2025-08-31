<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'period_id',
        'quota',
        'used',
        'notes',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}
