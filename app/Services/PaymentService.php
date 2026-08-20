<?php


namespace App\Services;

use Stripe\StripeClient;

class PaymentService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(
            config('services.stripe.secret')
        );
    }

    public function refundPayment(
        string $paymentIntentId,
        int $amount
    ) {
        return $this->stripe->refunds->create([
            'payment_intent' => $paymentIntentId,
            'amount' => $amount,
        ]);
    }
    public function createPaymentIntent(float $amount, string $currency, int $appointmentId, int $patientId) {
        return $this->stripe->paymentIntents->create([
            'amount' => (int) round($amount * 100),//كرمال تحويل من cents ل dollar لان سترايب بيستقبل cents
            'currency' => $currency,

            'metadata' => [
                'appointment_id' => $appointmentId,
                'patient_id' => $patientId,
            ],
        ]);
    }
}
