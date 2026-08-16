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
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\MedicalArticle;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use http\Env\Request;
use Illuminate\Validation\Rules\Enum;

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

    public function todayAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();
        $query->with('patient.user')
            ->where('doctor_id', auth()->user()->doctor->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->whereDate('start_at', today())
            ->orderBy('start_at')
            ->get();

        return response()->json(['appointments' => AppointmentResource::collection($appointments)]);
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
        $patient = PatientProfile::with('user')->findOrFail($validated['patient_id']);

        if ($validated['medical_record_access_code'] != $patient->medical_record_access_code) {
            return response()->json(['message' => 'incorrect code'], 401);
        }

        $reports = $patient->reports()->with('doctor.user',   'doctor.section', 'appointment','images')->latest()->get();

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

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('article_images', 'public');
        }

        $article = $user->doctor->articles()->create([
         //نضافت تلقائيا لان عملت عملية الاضافة من الدكتور    'doctor_id'=> $user->doctor->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'image' => $validated['image'] ?? null,
        ]);

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $article
        ], 201);
    }
    public function updateArticle(UpdateMedicalArticleRequest $request, $id) {
        $validated = $request->validated();

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

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('article_images', 'public');
        }

        $article->update([
            'title' => $validated['title'] ?? $article->title,
            'content' => $validated['content'] ?? $article->content,
            'category' => $validated['category'] ?? $article->category,
            'image' => $validated['image'] ?? $article->image,
        ]);

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => $article->fresh(),
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
    public function getArticlesByCategory(Request $request)
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
            'articles' => $articles,
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
            'articles' => $articles,
        ]);
    }

}
