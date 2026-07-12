<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // 💡 تم إضافة الـ Import المفقود
use Illuminate\Database\Eloquent\Model;

class LabRequest extends Model
{
    use HasFactory;

    protected $table = 'lab_requests';

    protected $fillable = [
        'doctor_profile_id',
        'patient_profile_id',
        'laboratory_profile_id',
        'doctor_notes',
        'status'
    ];

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_profile_id');
    }

    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function laboratory()
    {
        return $this->belongsTo(LaboratoryProfile::class, 'laboratory_profile_id');
    }

    public function tests()
    {
        return $this->belongsToMany(MedicalTest::class, 'lab_request_items')
            ->withPivot('result_value')
            ->withTimestamps();
    }
}
