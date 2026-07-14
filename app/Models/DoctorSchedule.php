<?php

namespace App\Models;

use App\Enums\Doctor\DayOfWeek;
use App\Enums\Schedule\ScheduleType;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];
    protected $casts = [
        'day_of_week' => DayOfWeek::class,
        'schedule_type'=>ScheduleType::class,

    ];
    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }
//    public function appointments()
//    {
//        return $this->hasMany(Appointment::class, 'doctor_schedule_id');
//    }
}
