<?php

namespace App\Http\Controllers;

use App\Enums\Appointment\AppointmentStatus;
use App\Http\Requests\Appointment\GetAppointmentsRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
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
    public function doctorServiceAreas($doctorId)
    {
        $doctor = DoctorProfile::find($doctorId);

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor not found'
            ], 404);
        }

        $areas = $doctor->serviceAreas()
            ->with('area')
            ->get()
            ->map(function ($doctorServiceArea) {
                return [
                    'id' => $doctorServiceArea->area->id,
                    'name' => $doctorServiceArea->area->name,
                ];
            });

        return response()->json([
            'doctor_id' => $doctor->id,
            'areas' => $areas,
        ]);
    }
    public function getMedicalAccessCode()
    {
        $patient = auth()->user()->patient;

        if (!$patient) {
            return response()->json([
                'message' => 'Patient profile not found'
            ], 404);
        }

        return response()->json([
            'medical_record_access_code' => $patient->medical_record_access_code,
        ]);
    }

    public function regenerateMedicalAccessCode()
    {
        $patient = auth()->user()->patient;

        if (!$patient) {
            return response()->json([
                'message' => 'Patient profile not found'
            ], 404);
        }

        $newCode = PatientProfile::generateMedicalAccessCode();

        $patient->update([
            'medical_record_access_code' => $newCode,
        ]);

        return response()->json([
            'message' => 'Medical access code changed successfully.',
            'medical_record_access_code' => $newCode,
        ]);
    }

    public function prescriptionsPdf()
    {
        $patient = auth()->user()->patient;

        if (!$patient) {
            return response()->json([
                'message' => 'Patient profile not found'
            ], 404);
        }

        $prescriptions = Prescription::with([
            'items',
            'doctor.user',
            'doctor.section',
            'appointment',
        ])
            ->where('patient_id', $patient->id)
            ->latest()
            ->get();

        $pdf = Pdf::loadView(
            'pdf.prescriptions',
            compact('patient', 'prescriptions')
        );

        return $pdf->stream('medical-prescriptions.pdf');
    }

    public function myMedicalRecord()
    {
        $patient = auth()->user()->patient;

        if (!$patient) {
            return response()->json([
                'message' => 'Patient profile not found'
            ], 404);
        }

        $patient->load('user');

        $reports = $patient->reports()
            ->with([
                'doctor.user',
                'doctor.section',
                'appointment',
                'images'
            ])
            ->latest()
            ->get();

        if ($reports->isEmpty()) {
            return response()->json([
                'message' => 'No reports found'
            ], 404);
        }

        $pdf = Pdf::loadView('pdf.medical_record', [
            'patient' => $patient,
            'reports' => $reports
        ]);

        return $pdf->download('medical_record.pdf');
    }
}
