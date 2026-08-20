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

        $earnings = $doctors->map(function ($doctor) use ($doctorPercentage, $clinicPercentage) {

            $totalCollected = Payment::where('status', PaymentStatus::Paid->value)
                ->whereHas('appointment', function ($query) use ($doctor) {
                    $query->where('doctor_id', $doctor->id);
                })
                ->sum('amount');

            $doctorEarnings = $totalCollected * ($doctorPercentage / 100);
            $clinicEarnings = $totalCollected * ($clinicPercentage / 100);

            return [
                'doctor_id' => $doctor->id,

                'doctor_name' => $doctor->user
                    ? $doctor->user->first_name . ' ' . $doctor->user->last_name
                    : null,

                'total_collected' => number_format($totalCollected, 2, '.', ''),

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
            ];
        });

        return response()->json([
            'earnings' => $earnings,

            'total_doctor_earnings' => number_format(
                $earnings->sum(fn ($doctor) => (float) $doctor['doctor_earnings']),
                2,
                '.',
                ''
            ),

            'total_clinic_earnings' => number_format(
                $earnings->sum(fn ($doctor) => (float) $doctor['clinic_earnings']),
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
            ->where('status', PaymentStatus::Paid->value)
            ->whereHas('appointment', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->orderByDesc('completed_at')
            ->get();

        $totalCollected = $payments->sum('amount');

        $doctorEarnings = $totalCollected * ($doctorPercentage / 100);
        $clinicEarnings = $totalCollected * ($clinicPercentage / 100);

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->user
                    ? $doctor->user->first_name . ' ' . $doctor->user->last_name
                    : null,
            ],

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

            'payments' => $payments->map(function ($payment) {
                return [
                    'payment_id' => $payment->id,
                    'appointment_id' => $payment->appointment_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'completed_at' => $payment->completed_at,
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

        $payments = Payment::with('appointment')
            ->where('status', PaymentStatus::Paid->value)
            ->whereHas('appointment', function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id);
            })
            ->orderByDesc('completed_at')
            ->get();

        $totalCollected = $payments->sum('amount');

        $doctorEarnings = $totalCollected * ($doctorPercentage / 100);

        $clinicEarnings = $totalCollected * ($clinicPercentage / 100);

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'name' => $user->first_name . ' ' . $user->last_name,
            ],

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

            'payments' => $payments->map(function ($payment) {
                return [
                    'payment_id' => $payment->id,
                    'appointment_id' => $payment->appointment_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'completed_at' => $payment->completed_at,
                ];
            }),
        ]);
    }
}
