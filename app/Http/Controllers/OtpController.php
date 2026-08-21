<?php

namespace App\Http\Controllers;

use App\Http\Resources\Auth\LoginResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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



        public function verifyPasswordResetOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required'],
            'otp_code' => ['required'],
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if (
            !$user->otp_code ||
            !$user->otp_expires_at ||
            $user->otp_expires_at <= now()
        ) {
            return response()->json([
                'message' => 'OTP expired'
            ], 403);
        }

        if ($user->otp_attempts >= 3) {

            $user->otp_attempts = 0;
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            return response()->json([
                'message' => 'You have exceeded your number of attempts.'
            ], 429);
        }

        if ($user->otp_code != $validated['otp_code']) {

            $user->otp_attempts += 1;
            $user->save();

            return response()->json([
                'message' => 'OTP code not matched'
            ], 422);
        }

        // OTP صحيح
        $user->otp_attempts = 0;
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // السماح بتغيير كلمة السر لمدة 10 دقائق
        Cache::put(
            'password_reset_verified:' . $user->phone,
            true,
            now()->addMinutes(10)
        );

        return response()->json([
            'message' => 'OTP verified successfully. You can now reset your password.'
        ], 200);
    }
    public function resendPasswordResetOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required'],
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $user->generateOtpCode();

        $user->otp_attempts = 0;
        $user->save();

        app(\App\Services\UltraMsgService::class)
            ->sendOtp($user->phone, $user->otp_code);

        return response()->json([
            'message' => 'Password reset OTP resent successfully.'
        ], 200);
    }
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required'],
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $user->generateOtpCode();

        $user->otp_attempts = 0;
        $user->save();

        app(\App\Services\UltraMsgService::class)
            ->sendOtp($user->phone, $user->otp_code);

        return response()->json([
            'message' => 'Password reset OTP sent successfully.'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $verified = Cache::get(
            'password_reset_verified:' . $validated['phone']
        );

        if (!$verified) {
            return response()->json([
                'message' => 'Please verify the OTP first.'
            ], 403);
        }

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        // استخدام واحد فقط
        Cache::forget(
            'password_reset_verified:' . $validated['phone']
        );

        return response()->json([
            'message' => 'Password reset successfully.'
        ], 200);
    }
}
