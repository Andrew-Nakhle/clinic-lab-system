<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Requests\Secretary\SearchPatientRequest;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\User;
use Illuminate\Http\Request;

class SecretaryController extends Controller
{
    public function searchPatient(SearchPatientRequest $request){
        $validated = $request->validated();
        $patient=User::where('phone',$validated['phone'])->
        where('status',UserStatus::Active)->
        with('patient')->
        first();


        if (!$patient) {
            return response()->json([
                'message' => 'Patient not found.'
            ], 404);
        }

        return response()->json(['patient'=>new RegisterResource($patient)]) ;
    }
}
