<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UltraMsgService
{
    public function sendOtp(string $phone, string $otp): bool
    {
        $url = "https://api.ultramsg.com/" . env('ULTRAMSG_INSTANCE_ID') . "/messages/chat";
        $token = env('ULTRAMSG_TOKEN');

        $response = Http::withoutVerifying()->asForm()->post(
            $url . "?token=" . $token,
            [
                'to' => $phone,
                'body' =>'your otp code is  ' . $otp,
            ]
        );

        return $response->successful();
    }
}
