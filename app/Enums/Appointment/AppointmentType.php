<?php

namespace App\Enums\Appointment;

enum AppointmentType :string
{
    case Home = 'home';

    case Clinic = 'clinic';
}
