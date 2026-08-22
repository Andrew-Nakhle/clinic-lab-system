<?php

namespace App\Http\Controllers;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Article\MedicalArticleCategory;
use App\Http\Requests\Appointment\GetAppointmentsRequest;
use App\Http\Requests\Doctor\CompleteAppointmentRequest;
use App\Http\Requests\Doctor\CreateMedicalArticleRequest;
use App\Http\Requests\Doctor\GetMedicalRecordRequest;
use App\Http\Requests\Doctor\UpdateMedicalArticleRequest;
use App\Http\Requests\Doctor\UpdateProfileRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\Article\ArticleResource;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\MedicalArticle;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;
use mysql_xdevapi\Collection;

class DoctorController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }

        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')
                ->store('profile_images', 'public');
        }

        $user->update([
            'first_name' => $validated['first_name'] ?? $user->first_name,
            'last_name'  => $validated['last_name'] ?? $user->last_name,
            'phone'      => $validated['phone'] ?? $user->phone,
            'gender'     => $validated['gender'] ?? $user->gender,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
            'profile_image' => $validated['profile_image'] ?? $user->profile_image,
        ]);

        // Update Doctor Profile
        if ($user->doctor) {

            $user->doctor->update([
                'experience_years' => $validated['experience_years']
                    ?? $user->doctor->experience_years,

                'bio' => $validated['bio']
                    ?? $user->doctor->bio,

                'section_id' => $validated['section_id']
                    ?? $user->doctor->section_id,

                'specialization' => $validated['specialization']
                    ?? $user->doctor->specialization,

                'qualification' => $validated['qualification']
                    ?? $user->doctor->qualification,
            ]);

            // Add new certifications
            $user->doctor->certifications()->delete();

            if ($request->hasFile('certifications')) {
                foreach ($request->file('certifications') as $certification) {
                    $path = $certification->store('certifications', 'public');

                    $user->doctor->certifications()->create([
                        'certification' => $path,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Profile updated successfully'
        ]);
    }

    public function todayPatientAppointments(GetAppointmentsRequest $request)
    {
        $doctor = auth()->user()->doctor;

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $query = Appointment::query();

        $query->with('patient.user')
            ->where('doctor_id', $doctor->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->whereDate('start_at', today())
            ->orderBy('start_at')
            ->get();

        $completedCount = $appointments
            ->where('status', AppointmentStatus::Completed)
            ->count();

        $pendingCount = $appointments
            ->where('status', AppointmentStatus::Booked)
            ->count();

        return response()->json([
            'completed_count' => $completedCount,
            'pending_count' => $pendingCount,

            'appointments' => $appointments->map(function ($appointment) {
                return [
                    'appointment_id' => $appointment->id,
                    'doctor_id' => $appointment->doctor_id,
                    'patient_id' => $appointment->patient_id,
                    'start_at' => $appointment->start_at,
                    'end_at' => $appointment->end_at,
                    'appointment_type' => $appointment->appointment_type,
                    'status' => $appointment->status,
                    'patient' => $appointment->patient,
                ];
            }),
        ]);
    }

    public function upcomingAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();
        $query->with('patient.user')
            ->where('doctor_id', auth()->user()->doctor->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->where('status', AppointmentStatus::Booked->value)
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->get();

        return response()->json(['appointments' => AppointmentResource::collection($appointments)]);
    }

    public function previousAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();
        $query->with('patient.user')
            ->where('doctor_id', auth()->user()->doctor->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->where('start_at', '<', now())
            ->whereIn('status', [
                AppointmentStatus::Completed->value,
                AppointmentStatus::Cancelled->value,
            ])
            ->orderByDesc('start_at')
            ->get();

        return response()->json(['appointments' => AppointmentResource::collection($appointments)]);
    }



    public function getMedicalRecord(GetMedicalRecordRequest $request)
    {
        $validated = $request->validated();

        $patient = PatientProfile::with('user')
            ->where(
                'medical_record_access_code',
                $validated['medical_record_access_code']
            )
            ->first();

        if (!$patient) {
            return response()->json([
                'message' => 'Incorrect medical record access code'
            ], 401);
        }

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

        // إنشاء PDF
        $pdf = Pdf::loadView('pdf.medical_record', [
            'patient' => $patient,
            'reports' => $reports
        ]);

        // اسم الملف
        $fileName = 'medical_records/medical_record_' . $patient->id . '_' . now()->timestamp . '.pdf';

        // حفظ داخل storage/app/public
        Storage::disk('public')->put(
            $fileName,
            $pdf->output()
        );

        $user = $patient->user;

        return response()->json([
            'patient' => [
                'id' => $patient->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'age' => Carbon::parse($user->birth_date)->age,
                'gender' => $user->gender,
                'notes' => $patient->notes,
            ],

            'pdf_url' => url('/storage/' . $fileName),
        ]);
    }
    public function getMedicalNotes($id)//pateint profile id
    {
       if(! $patient = PatientProfile::with('user')->find($id))
           return response()->json(['message' => 'patient not found'], 404);
        return response()->json([
            'patient' => $patient->user->first_name . ' ' . $patient->user->last_name,
            'medical_notes' => $patient->medical_notes,
        ]);
    }
    public function completeAppointment(CompleteAppointmentRequest $request)
    {
$validated = $request->validated();

        $appointment = Appointment::with(['doctor', 'patient'])
            ->findOrFail($validated['appointment_id']);
//check if this appointment belongs to doctor and patient
        if (
            $appointment->doctor_id != $validated['doctor_id'] ||
            $appointment->patient_id != $validated['patient_id']
        ) {
            return response()->json([
                'message' => 'This appointment does not belong to this doctor and patient.'
            ], 403);
        }

        if (Report::where('appointment_id', $validated['appointment_id'])->exists()) {
            return response()->json([
                'message' => 'A medical report already exists for this appointment.'
            ], 409);
        }
        $report = Report::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'appointment_id' => $validated['appointment_id'],
            'report' => $validated['report'],
        ]);
        if ($request->hasFile('report_images')) {

            foreach ($request->file('report_images') as $image) {

                $path = $image->store('report_images', 'public');

                $report->images()->create([
                    'image' => $path,
                ]);
            }
        }
            if (!empty($validated['medical_notes'])){
            $patient = PatientProfile::find($validated['patient_id']);

            $patient->update([
                'medical_notes' => $validated['medical_notes']
            ]);
        }
//هي بس بتتنفذ اذا باعت راشيتة بس
        if (!empty($validated['prescription'])) {

            $prescription = Prescription::create([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'appointment_id' => $validated['appointment_id'],
            ]);

            foreach ($validated['prescription'] as $item) {

                $prescription->items()->create([
                    'medicine_name' => $item['medicine_name'],
                    'instructions' => $item['instructions'],
                ]);
            }
        }
        $appointment->update([
            'status' => AppointmentStatus::Completed->value,
        ]);
        return response()->json([
            'message' => 'Appointment completed successfully'
        ]);
    }
    public function createArticle(CreateMedicalArticleRequest $request)
    {
        $validated = $request->validated();

        $user = auth()->user();

        if (!$user || !$user->doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $doctor = $user->doctor;

        if (!$doctor->section) {
            return response()->json([
                'message' => 'Doctor section not found'
            ], 404);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('article_images', 'public');
        }

        $article = $doctor->articles()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $doctor->section->name,
            'image' => $validated['image'] ?? null,
        ]);

        return response()->json([
            'message' => 'Article created successfully',
            'article' => new ArticleResource($article)
        ], 201);
    }
    public function updateArticle(UpdateMedicalArticleRequest $request, $id)
    {
        $validated = $request->validated();

        $user = auth()->user();

        if (!$user || !$user->doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $doctor = $user->doctor;

        if (!$doctor->section) {
            return response()->json([
                'message' => 'Doctor section not found'
            ], 404);
        }

        $article = $doctor->articles()->find($id);

        if (!$article) {
            return response()->json([
                'message' => 'Article not found'
            ], 404);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('article_images', 'public');
        }

        $article->update([
            'title' => $validated['title'] ?? $article->title,
            'content' => $validated['content'] ?? $article->content,
            'category' => $doctor->section->name,
            'image' => $validated['image'] ?? $article->image,
        ]);

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => new ArticleResource($article->fresh()),
        ]);
    }
    public function deleteArticle($id)
    {
        $user = auth()->user();

        if (!$user || !$user->doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $article = $user->doctor->articles()->find($id);

        if (!$article) {
            return response()->json([
                'message' => 'Article not found'
            ], 404);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully'
        ]);
    }
    public function getArticlesByCategory(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'category' => [
                'required',
                new Enum(MedicalArticleCategory::class),
            ],
        ]);

        $articles = MedicalArticle::where('category', $validated['category'])
            ->with('doctor.user')
            ->latest()
            ->get();

        return response()->json([
            'articles' => ArticleResource::collection($articles),
        ]);
    }
    public function getArticlesByDoctor($doctor_id)
    {
        if (!DoctorProfile::where('id', $doctor_id)->exists()) {
            return response()->json([
                'message' => 'Doctor not found'
            ], 404);
        }

        $articles = MedicalArticle::where('doctor_id', $doctor_id)
            ->latest()
            ->get();

        return response()->json([
            'articles' => ArticleResource::collection($articles),
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
    public function getMyPatientsReports()
    {
        $doctor = auth()->user()->doctor;

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $reports = Report::with([
            'patient.user',
            'appointment',
        ])
            ->where('doctor_id', $doctor->id)
            ->latest()
            ->get();

        if ($reports->isEmpty()) {
            return response()->json([
                'message' => 'No reports found'
            ], 404);
        }

        $patients = $reports
            ->groupBy('patient_id')
            ->map(function ($patientReports) {

                $patient = $patientReports->first()->patient;
                $user = $patient->user;

                // PDF يحتوي جميع تقارير هذا المريض
                $pdf = Pdf::loadView('pdf.medical_record', [
                    'patient' => $patient,
                    'reports' => $patientReports,
                ]);

                $fileName = 'medical_records/doctor_reports/'
                    . 'patient_' . $patient->id
                    . '_' . now()->timestamp
                    . '.pdf';

                Storage::disk('public')->put(
                    $fileName,
                    $pdf->output()
                );

                return [
                    'patient_id' => $patient->id,

                    'patient_name' =>
                        $user->first_name . ' ' . $user->last_name,

                    'gender' => $user->gender,

                    'reports_count' => $patientReports->count(),

                    'reports' => $patientReports->map(function ($report) {
                        return [
                            'report_id' => $report->id,

                            'report_date' => $report->appointment
                                ? $report->appointment->start_at
                                : $report->created_at,
                        ];
                    })->values(),

                    'all_reports_pdf_url' => url(
                        '/storage/' . $fileName
                    ),
                ];
            })
            ->values();

        return response()->json([
            'doctor_id' => $doctor->id,
            'patients' => $patients,
        ]);
    }
}
