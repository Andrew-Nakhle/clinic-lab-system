<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
<<<<<<< HEAD
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'secretary_id',
        'date',
        'time',
        'status',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient(){
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function secretary()
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }
=======

>>>>>>> cbf2b73a062e6a4a087972bd7a80a9052966c2dd
}
