<?php

namespace App\Enums\Appointment;

enum AppointmentMadeBy:string
{
    case Patient = 'patient';
    case Secretary = 'secretary';
}
