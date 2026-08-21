<?php

namespace App\Http\Controllers;

use App\Enums\Schedule\ScheduleType;
use App\Enums\UserStatus;
use App\Http\Requests\Auth\LoginManagersRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterAdminRequest;
use App\Http\Requests\Auth\RegisterDoctorRequest;
use App\Http\Requests\Auth\RegisterPatientRequest;
use App\Http\Requests\Auth\RegisterSecretaryRequest;
use App\Http\Requests\Patient\UpdatePatientProfileRequest;
use App\Http\Requests\RegisterLaboratoryRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\PatientProfile;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function registerPatient(RegisterPatientRequest $request)
    {
        $validated = $request->validated();
        $existingUser = User::where('phone', $validated['phone'])->first();

        if ($existingUser && $existingUser->status === UserStatus::Banned) {
            return response()->json([
                'message' => 'This phone number is banned from the system. You cannot create a new account.'
            ], 403);
        }

        if ($existingUser) {
            return response()->json([
                'message' => 'This phone number is already registered.'
            ], 422);
        }
        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('id_card')) {
            $validated['id_card'] = $request->file('id_card')->store('id_cards', 'public');
        }
        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'profile_image' => $validated['profile_image'] ?? null,
        ]);


        $user->assignRole('patient');
        $code = PatientProfile::generateMedicalAccessCode();

        $patient = $user->patient()->create([
            'blood_group' => $validated['blood_group'],
            'weight' => $validated['weight'],
            'tall' => $validated['tall'],
            'id_card' => $validated['id_card'],

            'medical_record_access_code' => $code,
        ]);


        $user->load('patient');
        return response()->json([
            'message' => 'Patient Registered Successfully',
            'user' => new RegisterResource($user)
        ], 201);
    }

    public function registerDoctor(RegisterDoctorRequest $request)
    {
        $validated = $request->validated();

        $data['profile_image'] = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('profile_images', 'public')
            : null;

//        $data['certification'] = $request->hasFile('certification')
//            ? $request->file('certification')->store('certifications', 'public')
//            : null;

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'profile_image' => $data['profile_image'],
        ]);

        $user->assignRole('doctor');
        $section = Section::findOrFail($validated['section_id']);
        $hasHomeVisit = collect($validated['schedules'])
            ->contains('schedule_type', ScheduleType::Home->value);
        $doctor = $user->doctor()->create([

            'section_id' => $validated['section_id'],
            'experience_years' => $validated['experience_years'],
            'consultation_fee' => $section->base_price,
            'home_visit_fee' => $hasHomeVisit
                ? $section->base_price * 2.5
                : null,
        ]);

        foreach ($validated['service_areas'] ?? [] as $areaId) {
            $doctor->serviceAreas()->create([
                'area_id' => $areaId,
            ]);
        }
        if ($request->hasFile('certifications')) {

            foreach ($request->file('certifications') as $certification) {

                $path = $certification->store('certifications', 'public');

                $doctor->certifications()->create([
                    'certification' => $path,
                ]);
            }
        }

        foreach ($validated['schedules'] as $schedule) {
            $doctor->schedules()->create([
                'schedule_type' => $schedule['schedule_type'],
                'day_of_week' => $schedule['day_of_week'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
            ]);
        }

        $user->load('doctor');
        return response()->json([
            'message' => 'Doctor Registered Successfully',
            'user' => new RegisterResource($user)
        ], 201);
    }


    public function registerSecretary(RegisterSecretaryRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $validated['profile_image'] = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('profile_images', 'public')
            : null;

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'profile_image' => $validated['profile_image'] ?? null,
        ]);

        $user->assignRole('secretary');
        $user->secretary()->create([
            'user_id' => $user->id,
            'section_id' => $validated['section_id'],
            'image' => $validated['profile_image'] ?? null,
        ]);

        $user->load('secretary');
        return response()->json([
            'message' => 'Secretary Registered Successfully',
            'user' => new RegisterResource($user)
        ], 201);
    }

    public function registerAdmin(RegisterAdminRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $user->assignRole('admin');

        return response()->json([
            'message' => 'Admin Registered Successfully',
            'user' => new RegisterResource($user)
        ]);
    }

    public function loginUser(LoginUserRequest $request)
    {
        $validated = $request->validated();
        $user = User::where('phone', $validated['phone'])->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }
        if ($user->status === UserStatus::Banned) {
            return response()->json([
                'message' => 'Your account has been banned from the system. You cannot log in.'
            ], 403);
        }

//        if (isset($user->status) && $user->status === UserStatus::Inactive) {
//            return response()->json(['message' => 'Your account is inactive. Please contact the administrator.'], 403);
//        }

        if (!Auth::attempt(['phone' => $validated['phone'], 'password' => $validated['password']])) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        $user->generateOtpCode();
        app(\App\Services\UltraMsgService::class)->sendOtp($user->phone, $user->otp_code);

        return response()->json(['message' => 'Please check your WhatsApp number.']);
    }

    public function loginManager(LoginManagersRequest $request)
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        if (isset($user->status) && $user->status === UserStatus::deleted) {
            return response()->json(['message' => 'Your account has been deleted.'], 403);
        }

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        if (!$user->hasAnyRole(['patient', 'doctor', 'admin', 'secretary', 'super_admin', 'laboratory'])) {
            return response()->json(['message' => 'You are not authorized to access this panel.'], 403);
        }

        $token = $user->createToken('authToken')->plainTextToken;
        return response()->json([
            'message' => 'Login Successful',
            'token' => $token,
            'user' => new LoginResource($user)
        ]);
    }

    public function labRegister(RegisterLaboratoryRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
        ]);

        $user->assignRole('laboratory');
        $user->laboratory()->create([
            'license_number' => $validated['license_number'],
            'section_id' => $validated['section_id'],
        ]);

        $user->load('laboratory');
        return response()->json(['message' => 'Laboratory Registered Successfully', 'user' => $user], 201);
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout Successful'], 200);
    }

    public function profile()
    {
        $user = auth()->user();
        return response()->json(new RegisterResource($user));
    }

    public function testAuth(Request $request)
    {
        return response()->json([
            'is_logged_in' => auth()->check(),
            'user' => auth()->user()
        ]);
    }

    public function updatePatientProfile(UpdatePatientProfileRequest $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }

        $validated = $request->validated();

        // Profile image
        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')
                ->store('profile_images', 'public');
        }

        // ID card
        if ($request->hasFile('id_card')) {
            $validated['id_card'] = $request->file('id_card')
                ->store('id_cards', 'public');
        }

        // Update user
        $user->update([
            'first_name' => $validated['first_name'] ?? $user->first_name,
            'last_name' => $validated['last_name'] ?? $user->last_name,
            'phone' => $validated['phone'] ?? $user->phone,
            'gender' => $validated['gender'] ?? $user->gender,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
            'profile_image' => $validated['profile_image'] ?? $user->profile_image,
        ]);

        // Update patient profile
        $user->patient->update([
            'tall' => $validated['tall'] ?? $user->patient->tall,
            'weight' => $validated['weight'] ?? $user->patient->weight,
            'blood_group' => $validated['blood_group'] ?? $user->patient->blood_group,
            'id_card' => $validated['id_card'] ?? $user->patient->id_card,
        ]);

        return response()->json([
            'message' => 'Patient profile updated successfully.'
        ]);
    }


}
