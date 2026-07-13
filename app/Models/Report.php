<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['patient_id', 'doctor_id', 'report'];
    public function patient(){
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
    public function doctor(){
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }
        public function appointment(){
        return $this->belongsTo(Appointment::class);
        }
}
