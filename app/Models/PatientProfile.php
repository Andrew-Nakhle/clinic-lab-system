<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientProfile extends Model
{
    use HasFactory;

    protected $table = 'patient_profiles';

    protected $fillable = [
        'user_id',
        'blood_group',
        'weight',
        'tall',
        'id_card',
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
     * علاقة البروفايل بمواعيد المريض (تم دمجها من كود زميلك)
     */
    public function patientAppointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }
}
