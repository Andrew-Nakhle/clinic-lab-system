<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorCertification extends Model
{
    protected $fillable = [
        'doctor_id',
        'certification',
    ];

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class);
    }
}
