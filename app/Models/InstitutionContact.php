<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'name',
        'email',
        'phone',
        'position',
        'is_primary',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
