<?php

namespace App\Http\Controllers;

use App\Http\Resources\Auth\LoginResource;
use App\Models\User;
use Illuminate\Http\Request;

class OtpController extends Controller
{

        public function verifyLoginOtp(Request $request ){
            $validated = $request->validate(['otp_code'=>['required'],
            'phone' =>['required']]);
            $user = User::where('phone', $validated['phone'])->first();

           if(!$user){
               return response()->json(['message'=>'User not found'],404);
           }
           elseif (!$user->otp_code || !$user->otp_expires_at || $user->otp_expires_at <= now()){
               return response()->json(['message'=>'OTP expired'],403);
           }
            elseif ($user->otp_attempts>=3){
                $user->otp_attempts=0;
                $user->otp_code = null;
                $user->otp_expires_at = null;
                $user->save();
                return response()->json(['message'=>'You have exceeded your number of attempts.'],429);
            }
           elseif ($user->otp_code!=$validated['otp_code']){
               $user->otp_attempts+=1;
               $user->save();
               return response()->json(['message'=>'OTP code not matched'],422,);
           }
           else{
    //           else mean this $user->otp_code==$validated['otp']
            $user->otp_attempts=0;
            $user->otp_code=null;
            $user->otp_expires_at=null;
            $user->save();
            $token=$user->createToken('token')->plainTextToken;
            return response()->json(['token'=>$token,
                'user'=>new LoginResource($user)],200);
        }

        }
    public function  resendLoginOtp(Request $request){
            $validated = $request->validate(['phone' =>['required']]);
            $user=User::where('phone', $validated['phone'])->first();
            if(!$user){
                return response()->json(['message'=>'User not found'],404);
            }
            $user->generateOtpCode();
            $otp=$user->otp_code;
            $user->otp_attempts=0;
            $user->save();

        app(\App\Services\UltraMsgService::class)
            ->sendOtp($user->phone, $otp);

        return response()->json(['message'=>'resend otp done successfully','otp'=>$otp],200);
    }
    public function  verifyPasswordResetOtp(){}
    public function  resendPasswordResetOtp(){}
}
