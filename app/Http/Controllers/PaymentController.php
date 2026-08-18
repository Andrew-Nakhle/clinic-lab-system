<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public  function __construct(){
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();//هاد body يلي بعتو سترايب
        $signature = $request->header('Stripe-Signature');//للتحقق انو الطلب صادر من سترايب يعني جايني من سترايب مو من شخص تاني

        try {//هي للتحقق انو التلت مدخلات صحيحين وجاين من سترايب اذا صحيحين بيعطيني event
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json([
                'message' => 'Invalid payload'
            ], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json([
                'message' => 'Invalid signature'
            ], 400);
        }

        // لسا ما عملنا أي تعديل على قاعدة البيانات

        return response()->json([
            'received' => true,
            'event' => $event->type,
        ]);
    }
}
