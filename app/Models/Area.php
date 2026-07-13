<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
    'name',
];
    public function Appointments(){
        return $this->hasMany(Appointment::class, 'area_id');
    }
    public function doctorServiceAreas()
    {
        return $this->hasMany(DoctorServiceArea::class, 'area_id');
    }
}
