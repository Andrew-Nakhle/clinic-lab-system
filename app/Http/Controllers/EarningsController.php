<?php

namespace App\Http\Controllers;

use App\Enums\Payment\PaymentStatus;
use App\Models\DoctorProfile;
use App\Models\Payment;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    /**
     * Get earnings for all doctors.
     * Admin + Super Admin
     */
    public function allDoctorsEarnings()
    {
        $doctorPercentage = 40;
        $clinicPercentage = 100 - $doctorPercentage;

        $doctors = DoctorProfile::with('user')->get();

        $earnings = $doctors->map(function ($doctor) use (
            $doctorPercentage,
            $clinicPercentage
        ) {

            $payments = Payment::whereIn('status', [
                PaymentStatus::Paid->value,
                PaymentStatus::Refunded->value,
            ])
                ->whereHas('appointment', function ($query) use ($doctor) {
                    $query->where('doctor_id', $doctor->id);
                })
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Original Amount
            |--------------------------------------------------------------------------
            */

            $originalTotal = $payments->sum(function ($payment) {
                return (float) $payment->amount;
            });

            /*
            |--------------------------------------------------------------------------
            | Refunded Amount
            |--------------------------------------------------------------------------
            */

            $totalRefunded = $payments->sum(function ($payment) {
                return (float) ($payment->refunded_amount ?? 0);
            });

            /*
            |--------------------------------------------------------------------------
            | Actual Collected Amount
            |--------------------------------------------------------------------------
            */

            $totalCollected = $payments->sum(function ($payment) {
                return (float) (
                    $payment->retained_amount
                    ?? $payment->amount
                );
            });

            /*
            |--------------------------------------------------------------------------
            | Earnings
            |--------------------------------------------------------------------------
            */

            $doctorEarnings =
                $totalCollected * ($doctorPercentage / 100);

            $clinicEarnings =
                $totalCollected * ($clinicPercentage / 100);

            return [
                'doctor_id' => $doctor->id,

                'doctor_name' => $doctor->user
                    ? $doctor->user->first_name . ' ' . $doctor->user->last_name
                    : null,

                'original_total' => number_format(
                    $originalTotal,
                    2,
                    '.',
                    ''
                ),

                'total_refunded' => number_format(
                    $totalRefunded,
                    2,
                    '.',
                    ''
                ),

                'total_collected' => number_format(
                    $totalCollected,
                    2,
                    '.',
                    ''
                ),

                'doctor_percentage' => $doctorPercentage,

                'doctor_earnings' => number_format(
                    $doctorEarnings,
                    2,
                    '.',
                    ''
                ),

                'clinic_percentage' => $clinicPercentage,

                'clinic_earnings' => number_format(
                    $clinicEarnings,
                    2,
                    '.',
                    ''
                ),

                'completed_payments' => $payments->count(),
            ];
        });

        return response()->json([

            'earnings' => $earnings,

            'total_original_amount' => number_format(
                $earnings->sum(
                    fn ($doctor) => (float) $doctor['original_total']
                ),
                2,
                '.',
                ''
            ),

            'total_refunded_amount' => number_format(
                $earnings->sum(
                    fn ($doctor) => (float) $doctor['total_refunded']
                ),
                2,
                '.',
                ''
            ),

            'total_collected' => number_format(
                $earnings->sum(
                    fn ($doctor) => (float) $doctor['total_collected']
                ),
                2,
                '.',
                ''
            ),

            'total_doctor_earnings' => number_format(
                $earnings->sum(
                    fn ($doctor) => (float) $doctor['doctor_earnings']
                ),
                2,
                '.',
                ''
            ),

            'total_clinic_earnings' => number_format(
                $earnings->sum(
                    fn ($doctor) => (float) $doctor['clinic_earnings']
                ),
                2,
                '.',
                ''
            ),
        ]);
    }

    /**
     * Get earnings for one doctor.
     * Admin + Super Admin
     */
    public function doctorEarnings($doctorId)
    {
        $doctorPercentage = 40;
        $clinicPercentage = 100 - $doctorPercentage;

        $doctor = DoctorProfile::with('user')->find($doctorId);

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor not found'
            ], 404);
        }

        $payments = Payment::with('appointment')
            ->whereIn('status', [
                PaymentStatus::Paid->value,
                PaymentStatus::Refunded->value,
            ])
            ->whereHas('appointment', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->orderByDesc('completed_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Financial Calculations
        |--------------------------------------------------------------------------
        */

        $originalTotal = $payments->sum(function ($payment) {
            return (float) $payment->amount;
        });

        $totalRefunded = $payments->sum(function ($payment) {
            return (float) ($payment->refunded_amount ?? 0);
        });

        $totalCollected = $payments->sum(function ($payment) {
            return (float) (
                $payment->retained_amount
                ?? $payment->amount
            );
        });

        $doctorEarnings =
            $totalCollected * ($doctorPercentage / 100);

        $clinicEarnings =
            $totalCollected * ($clinicPercentage / 100);

        return response()->json([

            'doctor' => [
                'id' => $doctor->id,

                'name' => $doctor->user
                    ? $doctor->user->first_name . ' ' . $doctor->user->last_name
                    : null,
            ],

            /*
            |--------------------------------------------------------------------------
            | Financial Summary
            |--------------------------------------------------------------------------
            */

            'original_total' => number_format(
                $originalTotal,
                2,
                '.',
                ''
            ),

            'total_refunded' => number_format(
                $totalRefunded,
                2,
                '.',
                ''
            ),

            'total_collected' => number_format(
                $totalCollected,
                2,
                '.',
                ''
            ),

            /*
            |--------------------------------------------------------------------------
            | Doctor Earnings
            |--------------------------------------------------------------------------
            */

            'doctor_percentage' => $doctorPercentage,

            'doctor_earnings' => number_format(
                $doctorEarnings,
                2,
                '.',
                ''
            ),

            /*
            |--------------------------------------------------------------------------
            | Clinic Earnings
            |--------------------------------------------------------------------------
            */

            'clinic_percentage' => $clinicPercentage,

            'clinic_earnings' => number_format(
                $clinicEarnings,
                2,
                '.',
                ''
            ),

            'completed_payments' => $payments->count(),

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */

            'payments' => $payments->map(function ($payment) {

                return [
                    'payment_id' => $payment->id,

                    'appointment_id' => $payment->appointment_id,

                    'original_amount' => number_format(
                        (float) $payment->amount,
                        2,
                        '.',
                        ''
                    ),

                    'refunded_amount' => number_format(
                        (float) ($payment->refunded_amount ?? 0),
                        2,
                        '.',
                        ''
                    ),

                    'retained_amount' => number_format(
                        (float) (
                            $payment->retained_amount
                            ?? $payment->amount
                        ),
                        2,
                        '.',
                        ''
                    ),

                    'currency' => $payment->currency,

                    'status' => $payment->status->value,

                    'completed_at' => $payment->completed_at,

                    'refunded_at' => $payment->refunded_at,
                ];
            }),
        ]);
    }


    /**
     * Get earnings for the currently authenticated doctor.
     * Doctor only
     */
    public function myEarnings()
    {
        $doctorPercentage = 40;
        $clinicPercentage = 100 - $doctorPercentage;

        $user = auth()->user();

        if (!$user || !$user->doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $doctor = $user->doctor;

        /*
        |--------------------------------------------------------------------------
        | Get Paid & Refunded Payments
        |--------------------------------------------------------------------------
        |
        | Paid:
        | المبلغ لم يتم إرجاعه.
        |
        | Refunded:
        | تم إرجاع جزء أو كل المبلغ.
        |
        */

        $payments = Payment::with('appointment')
            ->whereIn('status', [
                PaymentStatus::Paid->value,
                PaymentStatus::Refunded->value,
            ])
            ->whereHas('appointment', function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id);
            })
            ->orderByDesc('completed_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Calculate Earnings
        |--------------------------------------------------------------------------
        */

        $totalCollected = $payments->sum(function ($payment) {

            // المبلغ الذي بقي فعلياً بعد الـ refund
            return (float) (
                $payment->retained_amount ?? $payment->amount
            );
        });

        $totalRefunded = $payments->sum(function ($payment) {

            return (float) (
                $payment->refunded_amount ?? 0
            );
        });

        $doctorEarnings = $totalCollected * ($doctorPercentage / 100);

        $clinicEarnings = $totalCollected * ($clinicPercentage / 100);

        return response()->json([

            'doctor' => [
                'id' => $doctor->id,
                'name' => $user->first_name . ' ' . $user->last_name,
            ],

            /*
            |--------------------------------------------------------------------------
            | Financial Summary
            |--------------------------------------------------------------------------
            */

            'original_total' => number_format(
                $payments->sum('amount'),
                2,
                '.',
                ''
            ),

            'total_refunded' => number_format(
                $totalRefunded,
                2,
                '.',
                ''
            ),

            'total_collected' => number_format(
                $totalCollected,
                2,
                '.',
                ''
            ),

            /*
            |--------------------------------------------------------------------------
            | Doctor
            |--------------------------------------------------------------------------
            */

            'doctor_percentage' => $doctorPercentage,

            'doctor_earnings' => number_format(
                $doctorEarnings,
                2,
                '.',
                ''
            ),

            /*
            |--------------------------------------------------------------------------
            | Clinic
            |--------------------------------------------------------------------------
            */

            'clinic_percentage' => $clinicPercentage,

            'clinic_earnings' => number_format(
                $clinicEarnings,
                2,
                '.',
                ''
            ),

            'completed_payments' => $payments->count(),

            /*
            |--------------------------------------------------------------------------
            | Payment Details
            |--------------------------------------------------------------------------
            */

            'payments' => $payments->map(function ($payment) {

                return [
                    'payment_id' => $payment->id,

                    'appointment_id' => $payment->appointment_id,

                    'original_amount' => number_format(
                        (float) $payment->amount,
                        2,
                        '.',
                        ''
                    ),

                    'refunded_amount' => number_format(
                        (float) ($payment->refunded_amount ?? 0),
                        2,
                        '.',
                        ''
                    ),

                    'retained_amount' => number_format(
                        (float) (
                            $payment->retained_amount
                            ?? $payment->amount
                        ),
                        2,
                        '.',
                        ''
                    ),

                    'currency' => $payment->currency,

                    'status' => $payment->status->value,

                    'completed_at' => $payment->completed_at,

                    'refunded_at' => $payment->refunded_at,
                ];
            }),
        ]);
    }
}
