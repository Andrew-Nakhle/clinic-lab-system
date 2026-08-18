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
use Illuminate\Support\Facades\DB;


class AppointmentController extends Controller
{
    private function getAvailableSlots(DoctorProfile $doctor, $date, $appointment_type)
    {
        $dayOfWeek = Carbon::parse($date)->format('l');//هاد برجع اليوم كتابة
        $schedules = $doctor->schedules()->where('day_of_week', $dayOfWeek)->
        where('schedule_type', $appointment_type)->
        get();
        if ($schedules->isEmpty()) {
            return collect([]);
        }
        $slots = [];
        foreach ($schedules as $schedule) {
            $start_time = Carbon::parse($schedule->start_time);
            $end_time = Carbon::parse($schedule->end_time);
            //هي كرمال التقسم حسب الوقت يا 15 يا ساعة حسب نوع الموعد
            $slotDuration = $appointment_type === AppointmentType::Home->value
                ? 60
                : 15;

            while ($start_time->copy()->addMinutes($slotDuration)->lte($end_time)) {
                $slots[] = $start_time->format('H:i');
                $start_time->addMinutes($slotDuration);
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


    public function bookByPatient(BookAppointmentRequest $request, PaymentService $paymentService)
    {
        $validated = $request->validated();
        $doctor = DoctorProfile::find($validated['doctor_id']);

        $start_at = Carbon::parse($validated['start_at']);
        $duration = $validated['appointment_type'] === AppointmentType::Home->value
            ? 60
            : 15;

        $end_at = $start_at->copy()->addMinutes($duration);

        $date = $start_at->toDateString();//لحول الوقت لتاريخ ووقت استعملو مع تابع getAvailableSlots

        $availableSlots = $this->getAvailableSlots(
            $doctor,
            $date, $validated['appointment_type']
        );

        if (!$availableSlots->contains($start_at->format('H:i'))) {
            return response()->json(['message' => 'Appointment not available'], 409);
        }
        if ($validated['appointment_type'] == AppointmentType::Clinic->value) {
            $validated['price'] = $doctor->consultation_fee;
        } else
            $validated['price'] = $doctor->home_visit_fee;
        $validated['made_by'] = AppointmentMadeBy::Patient->value;
        $validated['end_at'] = $end_at;
        $validated['patient_id'] = auth()->user()->patient->id;

        $paymentMethod = $validated['payment_method'];
        try {
            return DB::transaction(function () use (
                $doctor,
                $validated,
                $start_at,
                $end_at,
                $paymentMethod,
                $paymentService
            ) {

                if ($paymentMethod === PaymentMethod::Online->value) {
                    $status = AppointmentStatus::PendingPayment->value;
                } else {
                    $status = AppointmentStatus::Booked->value;
                }


                $patient = auth()->user()->patient;
//هاد عملتو كرمال امنع المريض يحجز موعدين عند دكتورين بوقتين مختلفين
                $hasOverlappingAppointment = Appointment::where('patient_id', $patient->id)
                    ->whereIn('status', [AppointmentStatus::Booked->value,
                        AppointmentStatus::PendingPayment->value,])
                    ->where(function ($query) use ($start_at, $end_at) {
                        $query->where('start_at', '<', $end_at)
                            ->where('end_at', '>', $start_at);
                    })
                    ->exists();

                if ($hasOverlappingAppointment) {
                    return response()->json([
                        'message' => 'You already have an appointment at this time.'
                    ], 409);
                }


                $appointment = Appointment::create([
                    'doctor_id' => $doctor->id,
                    'patient_id' => $validated['patient_id'],
                    'start_at' => $start_at,
                    'end_at' => $end_at,
                    'appointment_type' => $validated['appointment_type'],
                    'made_by' => AppointmentMadeBy::Patient->value,
                    'price' => $validated['price'],
                    'status' => $status,
                ]);

                // Cash
                if ($paymentMethod === PaymentMethod::Cash->value) {

                    $payment = Payment::create([
                        'appointment_id' => $appointment->id,
                        'payment_method' => PaymentMethod::Cash->value,
                        'provider' => PaymentProvider::Cash->value,
                        'status' => PaymentStatus::Pending->value,
                        'amount' => $appointment->price,
                        'currency' => config('services.stripe.currency', 'usd'),
                    ]);

                    return response()->json([
                        'message' => 'Appointment created successfully.',
                        'appointment' => $appointment,
                        'payment' => $payment,
                    ], 201);
                }

                // Online
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

                    return response()->json([
                        'message' => 'Appointment created. Please complete the payment.',
                        'appointment' => $appointment,
                        'payment' => $payment,
                        'client_secret' => $paymentIntent->client_secret,
                    ], 201);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {

            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'This appointment was just booked by another patient.'
                ], 409);
            }

            throw $e;
        }
    }


    public function bookBySecretary(BookAppointmentBySecretaryRequest $request)
    {
        $validated = $request->validated();

        $doctor = DoctorProfile::find($validated['doctor_id']);

        $start_at = Carbon::parse($validated['start_at']);
        $duration = $validated['appointment_type'] === AppointmentType::Home->value
            ? 60
            : 15;

        $end_at = $start_at->copy()->addMinutes($duration);

        $date = $start_at->toDateString();

        $availableSlots = $this->getAvailableSlots(
            $doctor,
            $date,
            $validated['appointment_type']
        );

        if (!$availableSlots->contains($start_at->format('H:i'))) {
            return response()->json([
                'message' => 'Appointment not available'
            ], 409);
        }

// اذا في موعد متداخل يا برنس Andrew was hereeeeeee
        $hasOverlappingAppointment = Appointment::where('patient_id', $validated['patient_id'])
            ->whereIn('status', [
                AppointmentStatus::Booked->value,
                AppointmentStatus::PendingPayment->value,
            ])
            ->where(function ($query) use ($start_at, $end_at) {
                $query->where('start_at', '<', $end_at)
                    ->where('end_at', '>', $start_at);
            })
            ->exists();

        if ($hasOverlappingAppointment) {
            return response()->json([
                'message' => 'This patient already has an appointment at this time.'
            ], 409);
        }

        if ($validated['appointment_type'] === AppointmentType::Clinic->value) {
            $price = $doctor->consultation_fee;
        } else {
            $price = $doctor->home_visit_fee;
        }

        try {

            $appointment = DB::transaction(function () use (
                $doctor,
                $validated,
                $start_at,
                $end_at,
                $price
            ) {

                $appointment = Appointment::create([
                    'doctor_id' => $doctor->id,
                    'patient_id' => $validated['patient_id'],
                    'secretary_id' => auth()->user()->secretary->id,
                    'start_at' => $start_at,
                    'end_at' => $end_at,
                    'appointment_type' => $validated['appointment_type'],
                    'made_by' => AppointmentMadeBy::Secretary->value,
                    'price' => $price,
                    'status' => AppointmentStatus::Booked->value,
                ]);

                Payment::create([
                    'appointment_id' => $appointment->id,
                    'payment_method' => PaymentMethod::Cash->value,
                    'provider' => PaymentProvider::Cash->value,
                    'status' => PaymentStatus::Pending->value,
                    'amount' => $appointment->price,
                    'currency' => config('services.stripe.currency', 'usd'),
                ]);
                return $appointment;
            });
            return response()->json([
                'message' => 'Appointment Booked',
                'appointment' => $appointment,
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json(['message' => 'This appointment was just booked by another patient.'], 409);
            }
            throw $e;
        }
    }


    public function availableSlots(AvailableSlotsRequest $request)
    {
        $validated = $request->validated();
        $doctor = DoctorProfile::findOrFail($validated['doctor_id']);
        $availableSlots = $this->getAvailableSlots($doctor, $validated['date'], $validated['appointment_type']);
        return response()->json($availableSlots);

    }


    public function markAsAttended(Appointment $appointment)
    {
        if ($appointment->status !== AppointmentStatus::Booked) {
            return response()->json([
                'message' => 'This appointment cannot be marked as attended.'
            ], 409);
        }

        $appointment->update([
            'status' => AppointmentStatus::Completed->value,
        ]);

        $payment = $appointment->payment;

        if (
            $payment &&
            $payment->payment_method === PaymentMethod::Cash &&
            $payment->status === PaymentStatus::Pending
        ) {
            $payment->markAsCompleted(
                null,
                [
                    'payment_confirmed_by' => 'secretary',
                    'payment_type' => PaymentMethod::Cash->value,
                ]
            );
        }

        return response()->json([
            'message' => 'Patient attendance confirmed successfully.',
            'appointment' => $appointment->fresh(),
            'payment' => $payment?->fresh(),
        ], 200);
    }


    public function markAsNoShow(Appointment $appointment)
    {
        if ($appointment->status !== AppointmentStatus::Booked) {
            return response()->json([
                'message' => 'This appointment cannot be marked as no-show.'
            ], 409);
        }

        $appointment->update([
            'status' => AppointmentStatus::NoShow->value,
        ]);

        $payment = $appointment->payment;

        if (
            $payment &&
            $payment->status === PaymentStatus::Pending
        ) {
            $payment->markAsFailed([
                'reason' => 'Patient did not attend the appointment.',
                'marked_by' => 'secretary',
            ]);
        }

        return response()->json([
            'message' => 'Appointment marked as no-show.',
            'appointment' => $appointment->fresh(),
            'payment' => $payment?->fresh(),
        ], 200);
    }


}
