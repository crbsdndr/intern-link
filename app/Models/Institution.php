<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'province',
        'website',
        'industry',
        'notes',
        'photo',
    ];

    public function contacts()
    {
        return $this->hasMany(InstitutionContact::class);
    }

    public function quotas()
    {
        return $this->hasMany(InstitutionQuota::class);
    }
}
