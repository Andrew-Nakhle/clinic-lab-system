<?php

namespace App\Http\Controllers;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Payment\PaymentStatus;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Payment;
use Carbon\Carbon;
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
        $user = auth()->user();

        if (!$user || !$user->doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $doctor = $user->doctor;

        /*
        |--------------------------------------------------------------------------
        | Current Month
        |--------------------------------------------------------------------------
        */

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Doctor Fees
        |--------------------------------------------------------------------------
        */

        $consultationFee = (float) ($doctor->consultation_fee ?? 0);
        $homeVisitFee = (float) ($doctor->home_visit_fee ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Doctor Percentage
        |--------------------------------------------------------------------------
        */

        $doctorPercentage = 40;

        /*
        |--------------------------------------------------------------------------
        | Doctor Earnings Per Consultation
        |--------------------------------------------------------------------------
        */

        $doctorConsultationEarning =
            $consultationFee * ($doctorPercentage / 100);

        $doctorHomeVisitEarning =
            $homeVisitFee * ($doctorPercentage / 100);

        /*
        |--------------------------------------------------------------------------
        | Completed Appointments This Month
        |--------------------------------------------------------------------------
        */

        $appointments = Appointment::with('payment')
            ->where('doctor_id', $doctor->id)
            ->where(
                'status',
                AppointmentStatus::Completed->value
            )
            ->whereBetween('start_at', [
                $startOfMonth,
                $endOfMonth
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */

        $insideClinicCount = $appointments
            ->where('appointment_type', 'clinic')
            ->count();

        $outsideClinicCount = $appointments
            ->where('appointment_type', 'home')
            ->count();

        $totalConsultations =
            $insideClinicCount + $outsideClinicCount;

        /*
        |--------------------------------------------------------------------------
        | Calculate Total Amounts
        |--------------------------------------------------------------------------
        */

        $insideClinicTotal = 0;
        $outsideClinicTotal = 0;
        $totalRefunded = 0;

        foreach ($appointments as $appointment) {

            $amount = (float) $appointment->price;

            /*
            |--------------------------------------------------------------------------
            | Refund
            |--------------------------------------------------------------------------
            */

            if (
                $appointment->payment &&
                $appointment->payment->status === PaymentStatus::Refunded->value
            ) {
                $amount = (float) $appointment
                    ->payment
                    ->retained_amount;

                $totalRefunded += (float) (
                    $appointment->payment->refunded_amount ?? 0
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Inside Clinic
            |--------------------------------------------------------------------------
            */

            if ($appointment->appointment_type === 'clinic') {

                $insideClinicTotal += $amount;
            }

            /*
            |--------------------------------------------------------------------------
            | Outside Clinic / Home Visit
            |--------------------------------------------------------------------------
            */

            elseif ($appointment->appointment_type === 'home') {

                $outsideClinicTotal += $amount;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Total Consultation Amount
        |--------------------------------------------------------------------------
        */

        $totalConsultationsAmount =
            $insideClinicTotal + $outsideClinicTotal;

        /*
        |--------------------------------------------------------------------------
        | Doctor Earnings - 40%
        |--------------------------------------------------------------------------
        */

        $doctorInsideClinicEarnings =
            $insideClinicTotal *
            ($doctorPercentage / 100);

        $doctorOutsideClinicEarnings =
            $outsideClinicTotal *
            ($doctorPercentage / 100);

        $doctorTotalConsultationEarnings =
            $totalConsultationsAmount *
            ($doctorPercentage / 100);

        /*
        |--------------------------------------------------------------------------
        | Fixed Monthly Salary
        |--------------------------------------------------------------------------
        */

        $monthlySalary =
            (float) ($doctor->monthly_salary ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */

        $grandTotal =
            $monthlySalary +
            $doctorTotalConsultationEarnings;

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            /*
            |--------------------------------------------------------------------------
            | Doctor
            |--------------------------------------------------------------------------
            */

            'doctor_id' => $doctor->id,

            /*
            |--------------------------------------------------------------------------
            | Consultation Prices
            |--------------------------------------------------------------------------
            */

            'consultation_fee' => number_format(
                $consultationFee,
                2,
                '.',
                ''
            ),

            'home_visit_fee' => number_format(
                $homeVisitFee,
                2,
                '.',
                ''
            ),

            /*
            |--------------------------------------------------------------------------
            | This Month
            |--------------------------------------------------------------------------
            */

            'this_month' => [

                'month' => now()->month,

                'year' => now()->year,

                /*
                | Number of consultations
                */

                'inside_clinic_consultations' =>
                    $insideClinicCount,

                'outside_clinic_consultations' =>
                    $outsideClinicCount,

                'total_consultations' =>
                    $totalConsultations,

                /*
                | Total consultation prices
                */

                'inside_clinic_total' => number_format(
                    $insideClinicTotal,
                    2,
                    '.',
                    ''
                ),

                'outside_clinic_total' => number_format(
                    $outsideClinicTotal,
                    2,
                    '.',
                    ''
                ),

                'total_consultations_amount' => number_format(
                    $totalConsultationsAmount,
                    2,
                    '.',
                    ''
                ),

                /*
                | Doctor 40%
                */

                'doctor_percentage' =>
                    $doctorPercentage,

                'doctor_inside_clinic_earnings' => number_format(
                    $doctorInsideClinicEarnings,
                    2,
                    '.',
                    ''
                ),

                'doctor_outside_clinic_earnings' => number_format(
                    $doctorOutsideClinicEarnings,
                    2,
                    '.',
                    ''
                ),

                'doctor_total_consultation_earnings' => number_format(
                    $doctorTotalConsultationEarnings,
                    2,
                    '.',
                    ''
                ),

                /*
                | Refund
                */

                'total_refunded' => number_format(
                    $totalRefunded,
                    2,
                    '.',
                    ''
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | Monthly Payout
            |--------------------------------------------------------------------------
            */

            'monthly_payout' => [

                'fixed_monthly_salary' => number_format(
                    $monthlySalary,
                    2,
                    '.',
                    ''
                ),

                'consultations_earnings' => number_format(
                    $doctorTotalConsultationEarnings,
                    2,
                    '.',
                    ''
                ),

                'grand_total' => number_format(
                    $grandTotal,
                    2,
                    '.',
                    ''
                ),
            ],
        ]);
    }
}
