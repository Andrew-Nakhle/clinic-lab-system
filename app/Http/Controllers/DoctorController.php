<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request){
        $user = auth()->user();
$validated = $request->validated();
///////////////////
if(!$user){
    return response()->json(['message'=>'unauthorized.'],401);
}
///////////////////////
if($request->hasFile('profile_image')){
    $validated['profile_image']=$request->file('profile_image')->store('profile_images','public');
    $user->doctor->update(['profile_image' => $validated['profile_image']]);
}
/////////////////////////////
        if($request->hasFile('certification'))
        {
            $validated['certification'] =
                $request->file('certification')
                    ->store('certifications','public');
        }


/////////////////////////
        $user->update([
            'first_name' => $validated['first_name'] ?? $user->first_name,
            'last_name'  => $validated['last_name'] ?? $user->last_name,
            'phone'      => $validated['phone'] ?? $user->phone,
            'gender'     => $validated['gender'] ?? $user->gender,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
        ]);
//////////////
        $user->doctor->update(['experience_years' => $validated['experience_years'] ??$user->doctor->experience_years,
    'certification'=> $validated['certification']?? $user->doctor->certification,
    'bio'=>$validated['bio'] ?? $user->doctor->bio,
    'section_id'=>$validated['section_id'] ?? $user->doctor->section_id
]);
///////////////
        if(isset($validated['password']))
        {
            if (!Hash::check($request->current_password, $user->password))
            {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            $user->update([
                'password' => Hash::make($validated['password'])
            ]);
        }

        //////////////////
        return response()->json([
            'message' => 'Profile updated successfully'
        ]);
    }
}
