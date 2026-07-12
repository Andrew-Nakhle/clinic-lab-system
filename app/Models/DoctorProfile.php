<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
<<<<<<< HEAD
    protected $table = 'doctor_profiles';

    protected $fillable = [
        'user_id',
        'specialization',
        'qualification',
        'experience_years',
        'bio',
        'certification',
        'profile_image',
        'section_id',
    ];

    public function user()
    {
=======
    protected $table='doctor_profiles';
    protected $fillable=['user_id','specialization','qualification','experience_years','bio','certification','profile_image','section_id'];
    public function user(){
>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55
        return $this->belongsTo(User::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
<<<<<<< HEAD

    public function patients()
    {
        return $this->belongsToMany(PatientProfile::class, 'doctor_patient', 'doctor_id', 'patient_id');
=======
    public  function doctorAppointments(){
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class,'doctor_id');
>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55
    }
}

