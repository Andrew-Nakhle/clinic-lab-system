<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorServiceArea extends Model
{
    protected $fillable = ['area_id', 'doctor_id'];
    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
