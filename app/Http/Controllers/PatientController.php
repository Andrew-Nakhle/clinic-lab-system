<?php

namespace App\Http\Controllers;

use App\Enums\Appointment\AppointmentStatus;
use App\Http\Requests\Appointment\GetAppointmentsRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function todayPatientAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();

        $query->with('doctor.user')
            ->where('patient_id', auth()->user()->patient->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->whereDate('start_at', today())
            ->orderBy('start_at')
            ->get();

        return response()->json([
            'appointments' => AppointmentResource::collection($appointments)
        ]);
    }
    public function upcomingPatientAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();

        $query->with('doctor.user')
            ->where('patient_id', auth()->user()->patient->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->where('status', AppointmentStatus::Booked->value)
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->get();

        return response()->json([
            'appointments' => AppointmentResource::collection($appointments)
        ]);
    }
    public function previousPatientAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();

        $query->with('doctor.user')
            ->where('patient_id', auth()->user()->patient->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->where('start_at', '<', now())
            ->whereIn('status', [
                AppointmentStatus::Completed->value,
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
            ])
            ->orderByDesc('start_at')
            ->get();

        return response()->json([
            'appointments' => AppointmentResource::collection($appointments)
        ]);
    }
}
