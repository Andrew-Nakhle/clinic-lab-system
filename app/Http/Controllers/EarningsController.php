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


    public function myEarnings(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Month & Year
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $month = (int) $validated['month'];
        $year = (int) $validated['year'];

        /*
        |--------------------------------------------------------------------------
        | Doctor
        |--------------------------------------------------------------------------
        */

        $user = auth()->user();

        if (!$user || !$user->doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $doctor = $user->doctor;

        /*
        |--------------------------------------------------------------------------
        | Requested Month
        |--------------------------------------------------------------------------
        */

        $startOfMonth = Carbon::create(
            $year,
            $month,
            1
        )->startOfMonth();

        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Doctor Creation Date
        |--------------------------------------------------------------------------
        */

        if ($endOfMonth->lt($doctor->created_at)) {
            return response()->json([
                'message' => 'No appointments found. The doctor was not registered during this month.'
            ], 404);
        }

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
        | Get Completed Appointments
        |--------------------------------------------------------------------------
        |
        | فقط المعاينات المكتملة تدخل بحساب الأرباح.
        |
        */

        $appointments = Appointment::with('payment')
            ->where('doctor_id', $doctor->id)
            ->where(
                'status',
                AppointmentStatus::Completed->value
            )
            ->where('start_at', '>=', $doctor->created_at)
            ->whereBetween('start_at', [
                $startOfMonth,
                $endOfMonth
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Separate Clinic / Home Appointments
        |--------------------------------------------------------------------------
        */

        $insideClinicAppointments = $appointments->filter(
            fn ($appointment) =>
                $appointment->appointment_type === 'clinic'
        );

        $outsideClinicAppointments = $appointments->filter(
            fn ($appointment) =>
                $appointment->appointment_type === 'home'
        );

        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */

        $insideClinicCount =
            $insideClinicAppointments->count();

        $outsideClinicCount =
            $outsideClinicAppointments->count();

        $totalConsultations =
            $insideClinicCount +
            $outsideClinicCount;

        /*
        |--------------------------------------------------------------------------
        | Calculate Actual Collected Amount
        |--------------------------------------------------------------------------
        |
        | Cash:
        | payment status ممكن يضل pending لأن الدفع عند العيادة.
        |
        | Online Paid:
        | نأخذ amount.
        |
        | Refunded:
        | نأخذ retained_amount فقط.
        |
        */

        $insideClinicCollected = 0;

        $outsideClinicCollected = 0;

        $totalRefunded = 0;

        foreach ($appointments as $appointment) {

            $payment = $appointment->payment;

            /*
            |--------------------------------------------------------------------------
            | Default amount
            |--------------------------------------------------------------------------
            */

            $amount = (float) $appointment->price;

            /*
            |--------------------------------------------------------------------------
            | Refund
            |--------------------------------------------------------------------------
            */

            if (
                $payment &&
                $payment->status === PaymentStatus::Refunded->value
            ) {
                $amount = (float) $payment->retained_amount;

                $totalRefunded += (float) (
                    $payment->refunded_amount ?? 0
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Clinic
            |--------------------------------------------------------------------------
            */

            if ($appointment->appointment_type === 'clinic') {
                $insideClinicCollected += $amount;
            }

            /*
            |--------------------------------------------------------------------------
            | Home Visit
            |--------------------------------------------------------------------------
            */

            elseif ($appointment->appointment_type === 'home') {
                $outsideClinicCollected += $amount;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Doctor Earnings - 40%
        |--------------------------------------------------------------------------
        */

        $insideClinicEarnings =
            $insideClinicCollected *
            ($doctorPercentage / 100);

        $outsideClinicEarnings =
            $outsideClinicCollected *
            ($doctorPercentage / 100);

        /*
        |--------------------------------------------------------------------------
        | Total Consultation Earnings
        |--------------------------------------------------------------------------
        */

        $consultationsEarnings =
            $insideClinicEarnings +
            $outsideClinicEarnings;

        /*
        |--------------------------------------------------------------------------
        | This Month Total
        |--------------------------------------------------------------------------
        */

        $thisMonthTotal =
            $consultationsEarnings;

        /*
        |--------------------------------------------------------------------------
        | Monthly Salary
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
            $consultationsEarnings;

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

            'doctor' => [
                'id' => $doctor->id,

                'name' =>
                    $user->first_name . ' ' .
                    $user->last_name,
            ],

            /*
            |--------------------------------------------------------------------------
            | Requested Month
            |--------------------------------------------------------------------------
            */

            'month' => $month,

            'year' => $year,

            /*
            |--------------------------------------------------------------------------
            | Top Cards
            |--------------------------------------------------------------------------
            */

            'this_month_total' => number_format(
                $thisMonthTotal,
                2,
                '.',
                ''
            ),

            'outside_clinic' => number_format(
                $outsideClinicEarnings,
                2,
                '.',
                ''
            ),

            'inside_clinic' => number_format(
                $insideClinicEarnings,
                2,
                '.',
                ''
            ),

            /*
            |--------------------------------------------------------------------------
            | Earnings Overview
            |--------------------------------------------------------------------------
            */

            'earnings_overview' => [

                /*
                |--------------------------------------------------------------------------
                | Outside Clinic
                |--------------------------------------------------------------------------
                */

                'outside_clinic' => [

                    'number_of_consultations' =>
                        $outsideClinicCount,

                    'price_per_consultation' =>
                        number_format(
                            $homeVisitFee,
                            2,
                            '.',
                            ''
                        ),

                    'doctor_earning_per_consultation' =>
                        number_format(
                            $doctorHomeVisitEarning,
                            2,
                            '.',
                            ''
                        ),

                    'total_amount' =>
                        number_format(
                            $outsideClinicEarnings,
                            2,
                            '.',
                            ''
                        ),
                ],

                /*
                |--------------------------------------------------------------------------
                | Inside Clinic
                |--------------------------------------------------------------------------
                */

                'inside_clinic' => [

                    'number_of_consultations' =>
                        $insideClinicCount,

                    'price_per_consultation' =>
                        number_format(
                            $consultationFee,
                            2,
                            '.',
                            ''
                        ),

                    'doctor_earning_per_consultation' =>
                        number_format(
                            $doctorConsultationEarning,
                            2,
                            '.',
                            ''
                        ),

                    'total_amount' =>
                        number_format(
                            $insideClinicEarnings,
                            2,
                            '.',
                            ''
                        ),
                ],

                /*
                |--------------------------------------------------------------------------
                | Total
                |--------------------------------------------------------------------------
                */

                'total' => [

                    'number_of_consultations' =>
                        $totalConsultations,

                    'total_amount' =>
                        number_format(
                            $consultationsEarnings,
                            2,
                            '.',
                            ''
                        ),
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Monthly Payout
            |--------------------------------------------------------------------------
            */

            'monthly_payout' => [

                'fixed_monthly_salary' =>
                    number_format(
                        $monthlySalary,
                        2,
                        '.',
                        ''
                    ),

                'consultations_earnings' =>
                    number_format(
                        $consultationsEarnings,
                        2,
                        '.',
                        ''
                    ),

                'grand_total' =>
                    number_format(
                        $grandTotal,
                        2,
                        '.',
                        ''
                    ),
            ],

            /*
            |--------------------------------------------------------------------------
            | Doctor Percentage
            |--------------------------------------------------------------------------
            */

            'doctor_percentage' =>
                $doctorPercentage,

            /*
            |--------------------------------------------------------------------------
            | Refund
            |--------------------------------------------------------------------------
            */

            'total_refunded' =>
                number_format(
                    $totalRefunded,
                    2,
                    '.',
                    ''
                ),
        ]);
    }
}
