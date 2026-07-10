<?php

namespace App\Enums\Appointment;

enum AppointmentStatus : string
{
    case Booked = 'booked';         // محجوز
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';        // المريض ما حضر (اختياري)
}
