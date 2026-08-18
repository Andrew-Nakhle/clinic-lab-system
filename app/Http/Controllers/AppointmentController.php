<?php

namespace App\Http\Controllers;

use App\Enums\Appointment\AppointmentMadeBy;
use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\AppointmentType;
use App\Enums\Payment\PaymentMethod;
use App\Enums\Payment\PaymentProvider;
use App\Enums\Payment\PaymentStatus;
use App\Http\Requests\Appointment\AvailableSlotsRequest;
use App\Http\Requests\Appointment\BookAppointmentBySecretaryRequest;
use App\Http\Requests\Appointment\BookAppointmentRequest;
use App\Models\Appointment;
use App\Models\Area;
use App\Models\DoctorProfile;
use App\Models\Payment;
use App\Services\PaymentService;
use Carbon\Carbon;


class AppointmentController extends Controller
{
    private function getAvailableSlots(DoctorProfile $doctor, $date,$appointment_type)
    {
        $dayOfWeek = Carbon::parse($date)->format('l');//هاد برجع اليوم كتابة
        $schedules= $doctor->schedules()->where('day_of_week', $dayOfWeek)->
        where('schedule_type', $appointment_type)->
        get();
        if ($schedules->isEmpty()) {
            return collect([]);
        }
        $slots = [];
        foreach ($schedules as $schedule) {
            $start_time = Carbon::parse($schedule->start_time);
            $end_time = Carbon::parse($schedule->end_time);

            while ($start_time->copy()->addMinutes(15)->lte($end_time)) {
                $slots[] = $start_time->format('H:i');
                $start_time->addMinutes(15);
            }
        }
        $appointments = $doctor->doctorAppointments()->whereDate('start_at', $date)->
        where('status', '!=', AppointmentStatus::Cancelled->value)->
        get();
        $bookedSlots = $appointments->map(function ($appointment) {
            return carbon::parse($appointment->start_at)->format('H:i');
        });
        $availableSlots = [];
        foreach ($slots as $slot) {

            if (!$bookedSlots->contains($slot)) {
                $availableSlots[] = $slot;
            }
        }
            return collect($availableSlots);

    }


    public function bookByPatient(BookAppointmentRequest $request,PaymentService $paymentService)
    {
        $validated = $request->validated();
        $doctor = DoctorProfile::find($validated['doctor_id']);

        $start_at = Carbon::parse($validated['start_at']);
        $end_at = $start_at->copy()->addMinutes(15);
        $date = $start_at->toDateString();//لحول الوقت لتاريخ ووقت استعملو مع تابع getAvailableSlots

        $availableSlots = $this->getAvailableSlots(
            $doctor,
            $date,   $validated['appointment_type']
        );

        if (!$availableSlots->contains($start_at->format('H:i'))) {
            return response()->json(['message' => 'Appointment not available'], 409);
        }
if($validated['appointment_type']==AppointmentType::Clinic->value){
    $validated['price'] = $doctor->consultation_fee;
}
else
    $validated['price'] = $doctor->home_visit_fee;
        $validated['made_by'] = AppointmentMadeBy::Patient->value;
        $validated['end_at'] = $end_at;
        $validated['patient_id'] = auth()->user()->patient->id;

        $paymentMethod = $validated['payment_method'];

        if ($paymentMethod === PaymentMethod::Online->value) {
            $status = AppointmentStatus::PendingPayment->value;
        } else {
            $status = AppointmentStatus::Booked->value;
        }

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $validated['patient_id'],
            'start_at' => $start_at,
            'end_at' => $end_at,
            'appointment_type' => $validated['appointment_type'],
            'made_by' => AppointmentMadeBy::Patient->value,
            'price' =>  $validated['price'],
            'status' => $status,
        ]);
//لعمليات الكاش هي
        if ($paymentMethod === PaymentMethod::Cash->value) {

            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'payment_method' => PaymentMethod::Cash->value,
                'provider' => PaymentProvider::Cash->value,
                'status' => PaymentStatus::Pending->value,
                'amount' => $appointment->price,
                'currency' => config('services.stripe.currency', 'usd'),
            ]);

        }
//لعمليات ال online
        if ($paymentMethod === PaymentMethod::Online->value) {

            $currency = config('services.stripe.currency', 'usd');

            $paymentIntent = $paymentService->createPaymentIntent(
                $appointment->price,
                $currency,
                $appointment->id,
                $appointment->patient_id
            );
            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'payment_method' => PaymentMethod::Online->value,
                'provider' => PaymentProvider::Stripe->value,
                'status' => PaymentStatus::Pending->value,
                'amount' => $appointment->price,
                'currency' => $currency,
            ]); 
        }


        return response()->json([
            'message' => 'Appointment created. Please complete the payment.',
            'appointment' => $appointment,
            'payment' => $payment,
            'client_secret' => $paymentIntent->client_secret,
        ], 201);
    }

    public function bookBySecretary(BookAppointmentBySecretaryRequest $request)
    {
        $validated = $request->validated();
        $doctor = DoctorProfile::find($validated['doctor_id']);
        $start_at = Carbon::parse($validated['start_at']);
        $end_at = $start_at->copy()->addMinutes(15);
        $date = $start_at->toDateString();
        $availableSlots = $this->getAvailableSlots($doctor, $date,   $validated['appointment_type']);
        if (!$availableSlots->contains($start_at->format('H:i'))) {
            return response()->json(['message' => 'Appointment not available'], 409);
        }
        if($validated['appointment_type']==AppointmentType::Clinic->value){
            $validated['price'] = $doctor->consultation_fee;
        }
        else
            $validated['price'] = $doctor->home_visit_fee;
        $validated['end_at'] = $end_at;
        $validated['made_by'] = AppointmentMadeBy::Secretary->value;
        $validated['secretary_id'] = auth()->user()->secretary->id;

        $validated['status'] = AppointmentStatus::Booked->value;
        Appointment::create($validated);
        return response()->json(['message' => 'Appointment Booked']);
    }


    public function availableSlots(AvailableSlotsRequest $request)
    {
        $validated = $request->validated();
        $doctor = DoctorProfile::findOrFail($validated['doctor_id']);
        $availableSlots = $this->getAvailableSlots($doctor, $validated['date'], $validated['appointment_type']);
        return response()->json($availableSlots);

    }

}
