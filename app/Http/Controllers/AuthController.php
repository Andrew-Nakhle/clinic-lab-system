<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginManagersRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterAdminRequest;
use App\Http\Requests\Auth\RegisterDoctorRequest;
use App\Http\Requests\Auth\RegisterPatientRequest;
use App\Http\Requests\Auth\RegisterSecretaryRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\RolePermissionSeeder;
class AuthController extends Controller
{
public function registerPatient(RegisterPatientRequest $request){


    $validated = $request->validated();

    $validated['password']=Hash::make($validated['password']);

    if($request->hasFile('id_card')){
        $validated['id_card']=$request->file('id_card')->store('id_cards','public');
    }
    if ($request->hasFile('profile_image')){
        $validated['profile_image']=$request->file('profile_image')->store('profile_images','public');
    }

    $user=User::create([
        'first_name'=>$validated['first_name'],
        'last_name'=>$validated['last_name'],
        'phone'=>$validated['phone'],
        'password'=>$validated['password'],
//       'role'=>'patient',
        'gender'=>$validated['gender'],
            'birth_date'=>$validated['birth_date'],
    ]

    );
    $user->assignRole('patient');
    $user->patient()->create([
    'blood_group'=>$validated['blood_group'],
    'weight'=>$validated['weight'],
    'tall'=>$validated['tall'],
        'id_card'=>$validated['id_card'],
        'profile_image'=>$validated['profile_image']??null,

        ]);

    $user->load('patient');
    return response()->json([
        'message'=>'Patient Registered Successfully',
        'user'=>new registerResource($user)
    ],201);


}
public function registerDoctor(RegisterDoctorRequest $request){

    $validated=$request->validated();
    if ($request->hasFile('profile_image')){
        $validated['profile_image']=$request->file('profile_image')->store('profile_images','public');
    }

    $validated['password']=Hash::make($validated['password']);


    $user=User::create([
        'first_name'=>$validated['first_name'],
        'last_name'=>$validated['last_name'],
        'email'=>$validated['email'],
        'phone'=>$validated['phone'],
        'password'=>$validated['password'],
        'gender'=>$validated['gender'],
        'birth_date'=>$validated['birth_date'],

    ]);
    $user->assignRole('doctor');
    $user->doctor()->create([
        'profile_image'=>$validated['profile_image'],
        'section_id'=>$validated['section_id'],
        'certification'=>$validated['certification'],
        'experience_years'=>$validated['experience_years'],

        ]);
    $user->load('doctor');//load information from the model of user to bring the information about the patient
    return response()->json([
    'message'=>'Doctor Registered Successfully',
    'user'=>new registerResource($user)
    ],201);
}
public function registerSecretary(RegisterSecretaryRequest $request){
    $validated=$request->validated();
    $validated['password']=Hash::make($validated['password']);
    $user=User::create([
        'first_name'=>$validated['first_name'],
        'last_name'=>$validated['last_name'],
        'email'=>$validated['email'],
        'phone'=>$validated['phone'],
        'password'=>$validated['password'],
        'gender'=>$validated['gender'],
        'birth_date'=>$validated['birth_date'],
    ]);
    $user->assignRole('secretary');
    $user->secretary()->create([
      'user_id'=>$user->id,
    ]);
    $user->load('secretary');
    return response()->json([
        'message'=>'Secretary Registered Successfully',
        'user'=>new registerResource($user)
    ],201);
}
public function registerAdmin(RegisterAdminRequest $request)
{
$validated=$request->validated();
$validated['password']=Hash::make($validated['password']);
$user=User::create($validated);
$user->assignRole('admin');
return response()->json([
    'message'=>'Admin Registered Successfully',
    'user'=>new registerResource($user)
]);
}
public function loginUser(LoginUserRequest $request){
$validated=$request->validated();
if (!Auth::attempt($request->only('phone','password')))
  {
    return response()->json([
        'message'=>'Invalid Credentials'
    ],401);
}
$user=User::where('phone',$validated['phone'])->firstOrFail();
    $user->generateOtpCode();
$otp=$user->otp_code;
    app(\App\Services\UltraMsgService::class)->sendOtp($user->phone, $otp);
return response()->json([
'message'=>'please check your whatsapp number',
]);
//$token=$user->createToken('authToken')->plainTextToken;
//    return response()->json([
//        'message'=>'Login Successful',
//        'token' => $token,
//        'user' => $user
//    ]);
}

    public function loginManager(LoginManagersRequest $request)
    {
        $validated = $request->validated();
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }

        $user=User::where('email',$validated['email'])->firstOrFail();
        $token=$user->createToken('authToken')->plainTextToken;
        $user = auth()->user();
        if (! $user->hasAnyRole(['doctor','admin', 'manager', 'super_admin' , 'secretary'])) {
            return response()->json([
                'message' => 'You are not authorized to access manager panel'
            ], 403);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login Successful',
            'token'   => $token,
            'user'    => new LoginResource($user)
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Successful'
        ],200);
    }

    public function profile(){
    $user = auth()->user();
    return response()->json(new registerResource($user));
    }
}

