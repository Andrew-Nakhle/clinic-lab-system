<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
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

    /**
     * علاقة البروفايل بالمستخدم الأساسي
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * علاقة الطبيب بالقسم التابع له
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * علاقة الطبيب بمرضاه (Many-to-Many)
     */
    public function patients()
    {
        return $this->belongsToMany(PatientProfile::class, 'doctor_patient', 'doctor_id', 'patient_id');
    }

    /**
     * علاقة الطبيب بمواعيده (تم دمجها من كود زميلك)
     */
    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * علاقة الطبيب بجدول الدوام الخاص به (تم دمجها من كود زميلك)
     */
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
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
