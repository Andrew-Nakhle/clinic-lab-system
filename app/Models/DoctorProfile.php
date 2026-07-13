<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{

    protected $table='doctor_profiles';
    protected $fillable=['user_id','specialization','qualification','experience_years','bio','certification','profile_image','section_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function section(){
        return $this->belongsTo(Section::class);
    }
    public  function doctorAppointments(){
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class,'doctor_id');
    }
    public function serviceAreas()
    {
        return $this->hasMany(DoctorServiceArea::class, 'doctor_id');
    }
    public function reports()
    {
        return $this->hasMany(Report::class, 'doctor_id');
    }
}
