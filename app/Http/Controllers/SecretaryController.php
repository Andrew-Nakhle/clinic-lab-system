<?php

namespace App\Http\Controllers;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Payment\PaymentMethod;
use App\Enums\Payment\PaymentStatus;
use App\Enums\UserStatus;
use App\Events\NotificationSent;
use App\Http\Requests\Secretary\SearchPatientRequest;
use App\Http\Resources\Auth\RegisterResource;
use App\Http\Resources\DoctorResource;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function cancelAppointmentBySecretary(
        int $id,
        PaymentService $paymentService
    ) {
        $secretary = auth()->user()->secretary;

        if (!$secretary) {return response()->json(['message' => 'Secretary profile not found'], 404);}

        /*
        |--------------------------------------------------------------------------
        | Find Appointment
        |--------------------------------------------------------------------------
        | السكرتيرة تستطيع إلغاء أي موعد لدكتور ضمن نفس القسم
        */

        $appointment = Appointment::where('id', $id)
            ->whereHas('doctor', function ($query) use ($secretary) {
                $query->where('section_id', $secretary->section_id);
            })->with(['payment', 'patient.user',])->first();

        if (!$appointment) {
            return response()->json([
                'message' => 'Appointment not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Appointment Status
        |--------------------------------------------------------------------------
        */

        if (
            $appointment->status !== AppointmentStatus::Booked &&
            $appointment->status !== AppointmentStatus::PendingPayment
        ) {
            return response()->json([
                'message' => 'This appointment cannot be cancelled'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Appointment already started
        |--------------------------------------------------------------------------
        */

        if ($appointment->start_at <= now()) {
            return response()->json([
                'message' => 'This appointment cannot be cancelled because it has already started.'], 400);}

        return DB::transaction(function () use (
            $appointment,
            $paymentService
        ) {

            $payment = $appointment->payment;

            /*
            |--------------------------------------------------------------------------
            | Pending Payment
            |--------------------------------------------------------------------------
            */

            if ($appointment->status === AppointmentStatus::PendingPayment) {

                $appointment->update([
                    'status' => AppointmentStatus::Cancelled,
                ]);

                if ($payment) {$payment->update(['status' => PaymentStatus::Failed,]);}

                /*
                | Notification to Patient
                */

                broadcast(new NotificationSent(
                    $appointment->patient->user_id,
                    'Appointment Cancelled',
                    'Your appointment has been cancelled successfully.',
                    'appointment_cancelled',
                    [
                        'appointment_id' => $appointment->id,
                    ]
                ));

                return response()->json([
                    'message' => 'Appointment cancelled successfully.',
                    'refund_amount' => 0,
                    'retained_amount' => 0,
                    'appointment' => $appointment->fresh(),
                    'payment' => $payment?->fresh(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Cash Payment
            |--------------------------------------------------------------------------
            */

            if (
                $payment &&
                $payment->payment_method === PaymentMethod::Cash
            ) {

                $appointment->update([
                    'status' => AppointmentStatus::Cancelled,
                ]);

                $payment->update([
                    'status' => PaymentStatus::Failed,
                ]);

                /*
                | Notification to Patient
                */

                broadcast(new NotificationSent(
                    $appointment->patient->user_id,
                    'Appointment Cancelled',
                    'Your appointment has been cancelled successfully.',
                    'appointment_cancelled',
                    ['appointment_id' => $appointment->id,]
                ));

                return response()->json([
                    'message' => 'Appointment cancelled successfully.',
                    'refund_amount' => 0,
                    'retained_amount' => 0,
                    'appointment' => $appointment->fresh(),
                    'payment' => $payment->fresh(),
                ]);}

            /*
            |--------------------------------------------------------------------------
            | Online Payment
            |--------------------------------------------------------------------------
            */

            if (
                $payment &&
                $payment->payment_method === PaymentMethod::Online &&
                $payment->status === PaymentStatus::Paid
            ) {

                $hoursUntilAppointment =
                    now()->diffInMinutes($appointment->start_at, false) / 60;

                /*
                |--------------------------------------------------------------------------
                | Cancellation Policy
                |--------------------------------------------------------------------------
                |
                | 24 hours or more => 100% refund
                | 2 - 24 hours     => 50% refund
                | Less than 2 hours => 0% refund
                |
                */

                if ($hoursUntilAppointment >= 24) {

                    $refundPercentage = 100;

                } elseif ($hoursUntilAppointment >= 2) {

                    $refundPercentage = 50;

                } else {

                    $refundPercentage = 0;
                }

                $originalAmount = (float) $payment->amount;

                $refundAmount = round($originalAmount * ($refundPercentage / 100), 2);

                $retainedAmount = round($originalAmount - $refundAmount, 2);

                /*
                |--------------------------------------------------------------------------
                | Stripe Refund
                |--------------------------------------------------------------------------
                */

                if ($refundAmount > 0) {

                    $paymentService->refundPayment($payment->stripe_payment_intent_id, (int) round($refundAmount * 100));
                }

                /*
                |--------------------------------------------------------------------------
                | Update Payment
                |--------------------------------------------------------------------------
                */

                $payment->markAsRefunded(
                    $refundAmount,
                    [
                        'cancellation' => true,
                        'cancelled_by' => 'secretary',
                        'refund_percentage' => $refundPercentage,
                        'refunded_amount' => $refundAmount,
                        'retained_amount' => $retainedAmount,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Update Appointment
                |--------------------------------------------------------------------------
                */

                $appointment->update([
                    'status' => AppointmentStatus::Cancelled,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Notification to Patient
                |--------------------------------------------------------------------------
                */

                broadcast(new NotificationSent(
                    $appointment->patient->user_id,
                    'Appointment Cancelled',
                    'Your appointment has been cancelled successfully.',
                    'appointment_cancelled',
                    [
                        'appointment_id' => $appointment->id,
                        'refund_amount' => $refundAmount,
                        'retained_amount' => $retainedAmount,
                    ]
                ));

                return response()->json([
                    'message' => 'Appointment cancelled successfully.',

                    'cancellation_policy' => [
                        'refund_percentage' => $refundPercentage,

                        'refund_amount' => number_format(
                            $refundAmount,
                            2,
                            '.',
                            ''
                        ),

                        'retained_amount' => number_format(
                            $retainedAmount,
                            2,
                            '.',
                            ''
                        ),
                    ],

                    'appointment' => $appointment->fresh(),

                    'payment' => $payment->fresh(),
                ]);
            }
            /*
            |--------------------------------------------------------------------------
            | Unknown Payment State
            |--------------------------------------------------------------------------
            */
            return response()->json([
                'message' => 'Payment state does not allow cancellation.'
            ], 400);
        });
    }

    public function getDoctorsBySection(int $sectionId)
    {
        $doctors = DoctorProfile::with([
            'user',
            'section',
            'schedules',
            'certifications',
            'serviceAreas.area'
        ])
            ->where('section_id', $sectionId)
            ->get();

        if ($doctors->isEmpty()) {
            return response()->json([
                'message' => 'No doctors found in this section.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'section' => [
                'id' => $doctors->first()->section->id,
                'name' => $doctors->first()->section->name,
            ],
            'doctors' => DoctorResource::collection($doctors),
        ]);
    }
}
