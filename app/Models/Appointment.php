<?php

namespace App\Models;

use App\Enums\Appointment\AppointmentMadeBy;
use App\Enums\Appointment\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable= ['doctor_id','doctor_schedule_id','patient_id','secretary_id','start_at','end_at','status','made_by','price'];

    protected $casts = [
        'made_by' => AppointmentMadeBy::class,
        'status' => AppointmentStatus::class,
    ];


    public function doctor(){
         return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }
    public function schedule(){
        return $this->belongsTo(DoctorSchedule::class, 'doctor_schedule_id');
    }
    public function patient(){
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
    public function secretary(){
        return $this->belongsTo(SecretaryProfile::class, 'secretary_id');
    }


}
