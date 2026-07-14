<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorServiceArea extends Model
{
    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
