<?php

namespace App\Http\Controllers;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\AppointmentType;
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



    public function allDoctorsEarnings(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Month & Year
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;

        $startOfMonth = Carbon::create($year, $month, 1)
            ->startOfMonth();

        $endOfMonth = Carbon::create($year, $month, 1)
            ->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Doctor Percentage
        |--------------------------------------------------------------------------
        */

        $doctorPercentage = 40;

        /*
        |--------------------------------------------------------------------------
        | Global Totals
        |--------------------------------------------------------------------------
        */

        $allInsideClinicConsultations = 0;
        $allOutsideClinicConsultations = 0;

        $allInsideClinicTotal = 0;
        $allOutsideClinicTotal = 0;

        $allDoctorInsideEarnings = 0;
        $allDoctorOutsideEarnings = 0;
        $allDoctorConsultationEarnings = 0;

        $allRefunded = 0;

        /*
        |--------------------------------------------------------------------------
        | Get All Doctors
        |--------------------------------------------------------------------------
        */

        $doctors = DoctorProfile::with('user')->get();

        $doctorsEarnings = $doctors->map(function ($doctor) use (
            $startOfMonth,
            $endOfMonth,
            $month,
            $year,
            $doctorPercentage,
            &$allInsideClinicConsultations,
            &$allOutsideClinicConsultations,
            &$allInsideClinicTotal,
            &$allOutsideClinicTotal,
            &$allDoctorInsideEarnings,
            &$allDoctorOutsideEarnings,
            &$allDoctorConsultationEarnings,
            &$allRefunded
        ) {

            /*
            |--------------------------------------------------------------------------
            | Doctor Fees
            |--------------------------------------------------------------------------
            */

            $consultationFee =
                (float) ($doctor->consultation_fee ?? 0);

            $homeVisitFee =
                (float) ($doctor->home_visit_fee ?? 0);

            /*
            |--------------------------------------------------------------------------
            | Doctor Monthly Salary
            |--------------------------------------------------------------------------
            */

            $monthlySalary =
                (float) ($doctor->monthly_salary ?? 0);

            /*
            |--------------------------------------------------------------------------
            | Doctor Creation Date
            |--------------------------------------------------------------------------
            */

            $doctorCreatedAt = $doctor->created_at;

            /*
            |--------------------------------------------------------------------------
            | Doctor did not exist during selected month
            |--------------------------------------------------------------------------
            */

            if (
                $startOfMonth->lt(
                    Carbon::parse($doctorCreatedAt)->startOfMonth()
                )
            ) {

                return [
                    'doctor_id' => $doctor->id,

                    'doctor_name' => $doctor->user
                        ? $doctor->user->first_name . ' ' .
                        $doctor->user->last_name
                        : null,

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

                    'monthly_salary' => number_format(
                        $monthlySalary,
                        2,
                        '.',
                        ''
                    ),

                    'this_month' => [
                        'month' => $month,
                        'year' => $year,

                        'inside_clinic_consultations' => 0,
                        'outside_clinic_consultations' => 0,
                        'total_consultations' => 0,

                        'inside_clinic_total' => '0.00',
                        'outside_clinic_total' => '0.00',
                        'total_consultations_amount' => '0.00',

                        'doctor_percentage' => $doctorPercentage,

                        'doctor_inside_clinic_earnings' => '0.00',
                        'doctor_outside_clinic_earnings' => '0.00',
                        'doctor_total_consultation_earnings' => '0.00',

                        'total_refunded' => '0.00',
                    ],

                    'monthly_payout' => [
                        'fixed_monthly_salary' => number_format(
                            $monthlySalary,
                            2,
                            '.',
                            ''
                        ),

                        'consultations_earnings' => '0.00',

                        'grand_total' => number_format(
                            $monthlySalary,
                            2,
                            '.',
                            ''
                        ),
                    ],
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Get Completed Appointments
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
                ->where(
                    'appointment_type',
                    AppointmentType::Clinic->value
                )
                ->count();

            $outsideClinicCount = $appointments
                ->where(
                    'appointment_type',
                    AppointmentType::Home->value
                )
                ->count();

            $totalConsultations =
                $insideClinicCount +
                $outsideClinicCount;

            /*
            |--------------------------------------------------------------------------
            | Amounts
            |--------------------------------------------------------------------------
            */

            $insideClinicTotal = 0;
            $outsideClinicTotal = 0;
            $totalRefunded = 0;

            foreach ($appointments as $appointment) {

                /*
                |--------------------------------------------------------------------------
                | Original appointment price
                |--------------------------------------------------------------------------
                */

                $amount = (float) $appointment->price;

                /*
                |--------------------------------------------------------------------------
                | Refund
                |--------------------------------------------------------------------------
                */

                if (
                    $appointment->payment &&
                    $appointment->payment->status ===
                    PaymentStatus::Refunded
                ) {

                    $amount = (float) (
                        $appointment->payment->retained_amount ?? 0
                    );

                    $totalRefunded += (float) (
                        $appointment->payment->refunded_amount ?? 0
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Inside Clinic
                |--------------------------------------------------------------------------
                */

                if (
                    $appointment->appointment_type ===
                    AppointmentType::Clinic
                ) {

                    $insideClinicTotal += $amount;
                }

                /*
                |--------------------------------------------------------------------------
                | Outside Clinic / Home
                |--------------------------------------------------------------------------
                */

                elseif (
                    $appointment->appointment_type ===
                    AppointmentType::Home
                ) {

                    $outsideClinicTotal += $amount;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Total Consultation Amount
            |--------------------------------------------------------------------------
            */

            $totalConsultationsAmount =
                $insideClinicTotal +
                $outsideClinicTotal;

            /*
            |--------------------------------------------------------------------------
            | Doctor 40%
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
            | Grand Total
            |--------------------------------------------------------------------------
            */

            $grandTotal =
                $monthlySalary +
                $doctorTotalConsultationEarnings;

            /*
            |--------------------------------------------------------------------------
            | Add to Global Totals
            |--------------------------------------------------------------------------
            */

            $allInsideClinicConsultations +=
                $insideClinicCount;

            $allOutsideClinicConsultations +=
                $outsideClinicCount;

            $allInsideClinicTotal +=
                $insideClinicTotal;

            $allOutsideClinicTotal +=
                $outsideClinicTotal;

            $allDoctorInsideEarnings +=
                $doctorInsideClinicEarnings;

            $allDoctorOutsideEarnings +=
                $doctorOutsideClinicEarnings;

            $allDoctorConsultationEarnings +=
                $doctorTotalConsultationEarnings;

            $allRefunded +=
                $totalRefunded;

            /*
            |--------------------------------------------------------------------------
            | Return Doctor
            |--------------------------------------------------------------------------
            */

            return [
                'doctor_id' => $doctor->id,

                'doctor_name' => $doctor->user
                    ? $doctor->user->first_name . ' ' .
                    $doctor->user->last_name
                    : null,

                /*
                |--------------------------------------------------------------------------
                | Doctor Fees
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
                | Monthly Salary
                |--------------------------------------------------------------------------
                */

                'monthly_salary' => number_format(
                    $monthlySalary,
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

                    'month' => $month,

                    'year' => $year,

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
                    | Total amounts
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
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Global Totals
        |--------------------------------------------------------------------------
        */

        $allTotalConsultations =
            $allInsideClinicConsultations +
            $allOutsideClinicConsultations;

        $allTotalConsultationsAmount =
            $allInsideClinicTotal +
            $allOutsideClinicTotal;

        $allGrandTotal =
            $doctorsEarnings->sum(function ($doctor) {
                return (float) str_replace(
                    ',',
                    '',
                    $doctor['monthly_payout']['grand_total']
                );
            });

        /*
        |--------------------------------------------------------------------------
        | Final Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'month' => $month,

            'year' => $year,

            /*
            |--------------------------------------------------------------------------
            | Doctors
            |--------------------------------------------------------------------------
            */

            'doctors' => $doctorsEarnings->values(),

            /*
            |--------------------------------------------------------------------------
            | All Doctors Totals
            |--------------------------------------------------------------------------
            */

            'all_doctors_totals' => [

                'inside_clinic_consultations' =>
                    $allInsideClinicConsultations,

                'outside_clinic_consultations' =>
                    $allOutsideClinicConsultations,

                'total_consultations' =>
                    $allTotalConsultations,

                'inside_clinic_total' => number_format(
                    $allInsideClinicTotal,
                    2,
                    '.',
                    ''
                ),

                'outside_clinic_total' => number_format(
                    $allOutsideClinicTotal,
                    2,
                    '.',
                    ''
                ),

                'total_consultations_amount' => number_format(
                    $allTotalConsultationsAmount,
                    2,
                    '.',
                    ''
                ),

                /*
                |--------------------------------------------------------------------------
                | Doctors 40%
                |--------------------------------------------------------------------------
                */

                'doctor_percentage' =>
                    $doctorPercentage,

                'total_doctor_inside_clinic_earnings' =>
                    number_format(
                        $allDoctorInsideEarnings,
                        2,
                        '.',
                        ''
                    ),

                'total_doctor_outside_clinic_earnings' =>
                    number_format(
                        $allDoctorOutsideEarnings,
                        2,
                        '.',
                        ''
                    ),

                'total_doctor_consultation_earnings' =>
                    number_format(
                        $allDoctorConsultationEarnings,
                        2,
                        '.',
                        ''
                    ),

                'total_refunded' => number_format(
                    $allRefunded,
                    2,
                    '.',
                    ''
                ),

                /*
                |--------------------------------------------------------------------------
                | Grand Total
                |--------------------------------------------------------------------------
                */

                'total_monthly_salaries' => number_format(
                    $doctors->sum(
                        fn ($doctor) =>
                        (float) ($doctor->monthly_salary ?? 0)
                    ),
                    2,
                    '.',
                    ''
                ),

                'grand_total' => number_format(
                    $allGrandTotal,
                    2,
                    '.',
                    ''
                ),
            ],
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
            return (float)$payment->amount;
        });

        $totalRefunded = $payments->sum(function ($payment) {
            return (float)($payment->refunded_amount ?? 0);
        });

        $totalCollected = $payments->sum(function ($payment) {
            return (float)(
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
                        (float)$payment->amount,
                        2,
                        '.',
                        ''
                    ),

                    'refunded_amount' => number_format(
                        (float)($payment->refunded_amount ?? 0),
                        2,
                        '.',
                        ''
                    ),

                    'retained_amount' => number_format(
                        (float)(
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
        $user = auth()->user();

        if (!$user || !$user->doctor) {
            return response()->json([
                'message' => 'Doctor profile not found'
            ], 404);
        }

        $doctor = $user->doctor;

        /*
        |--------------------------------------------------------------------------
        | Validate Month
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;

        $startOfMonth = Carbon::create($year, $month, 1)
            ->startOfMonth();

        $endOfMonth = Carbon::create($year, $month, 1)
            ->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Doctor Creation Date
        |--------------------------------------------------------------------------
        */

        $doctorCreatedAt = $doctor->created_at;

        if (
            $startOfMonth->lt(
                Carbon::parse($doctorCreatedAt)->startOfMonth()
            )
        ) {
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
        | Get Completed Appointments
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
            ->where('appointment_type', AppointmentType::Clinic->value)
            ->count();

        $outsideClinicCount = $appointments
            ->where('appointment_type', AppointmentType::Home->value)
            ->count();

        $totalConsultations =
            $insideClinicCount + $outsideClinicCount;

        /*
        |--------------------------------------------------------------------------
        | Amounts
        |--------------------------------------------------------------------------
        */

        $insideClinicTotal = 0;
        $outsideClinicTotal = 0;
        $totalRefunded = 0;

        foreach ($appointments as $appointment) {

            /*
            |--------------------------------------------------------------------------
            | Original appointment price
            |--------------------------------------------------------------------------
            */

            $amount = (float) $appointment->price;

            /*
            |--------------------------------------------------------------------------
            | Refund
            |--------------------------------------------------------------------------
            */

            if (
                $appointment->payment &&
                $appointment->payment->status === PaymentStatus::Refunded
            ) {
                $amount = (float) (
                    $appointment->payment->retained_amount ?? 0
                );

                $totalRefunded += (float) (
                    $appointment->payment->refunded_amount ?? 0
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Inside Clinic
            |--------------------------------------------------------------------------
            */

            if (
                $appointment->appointment_type === AppointmentType::Clinic
            ) {
                $insideClinicTotal += $amount;
            }

            /*
            |--------------------------------------------------------------------------
            | Outside Clinic / Home Visit
            |--------------------------------------------------------------------------
            */

            elseif (
                $appointment->appointment_type === AppointmentType::Home
            ) {
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
            $insideClinicTotal * ($doctorPercentage / 100);

        $doctorOutsideClinicEarnings =
            $outsideClinicTotal * ($doctorPercentage / 100);

        $doctorTotalConsultationEarnings =
            $totalConsultationsAmount * ($doctorPercentage / 100);

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
            $monthlySalary + $doctorTotalConsultationEarnings;

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'doctor_id' => $doctor->id,

            /*
            |--------------------------------------------------------------------------
            | Doctor Fees
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
            | Selected Month
            |--------------------------------------------------------------------------
            */

            'this_month' => [

                'month' => $month,

                'year' => $year,

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
                | Total amounts
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

