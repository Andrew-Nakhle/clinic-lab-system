<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Model;
=======
use App\Enums\Appointment\AppointmentMadeBy;
use App\Enums\Appointment\AppointmentStatus;
use App\Http\Requests\Appointment\AvailableSlotsRequest;
use App\Http\Requests\Appointment\BookAppointmentBySecretaryRequest;
use App\Http\Requests\Appointment\BookAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use Carbon\Carbon;

>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55

class Appointment extends Model
{
<<<<<<< HEAD
<<<<<<< HEAD
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'secretary_id',
        'date',
        'time',
        'status',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient(){
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function secretary()
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }
=======

>>>>>>> cbf2b73a062e6a4a087972bd7a80a9052966c2dd
=======
    private function getAvailableSlots(DoctorProfile $doctor, $date)
    {
        $dayOfWeek = Carbon::parse($date)->format('l');//هاد برجع اليوم كتابة
        $schedules= $doctor->schedules()->where('day_of_week', $dayOfWeek)->get();
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


    public function bookByPatient(BookAppointmentRequest $request)
    {
        $validated = $request->validated();
        $doctor = DoctorProfile::find($validated['doctor_id']);

        $start_at = Carbon::parse($validated['start_at']);
        $end_at = $start_at->copy()->addMinutes(15);
        $date = $start_at->toDateString();

        $availableSlots = $this->getAvailableSlots(
            $doctor,
            $date
        );

        if (!$availableSlots->contains($start_at->format('H:i'))) {
            return response()->json(['message' => 'Appointment not available'], 409);
        }

        $validated['made_by'] = AppointmentMadeBy::Patient->value;
        $validated['end_at'] = $end_at;
        $validated['patient_id'] = auth()->user()->patient->id;
        $validated['price'] = $doctor->consultation_fee;
        $validated['status'] = AppointmentStatus::Booked->value;
        Appointment::create($validated);
        return response()->json(['message' => 'Appointment Booked']);
    }

    public function bookBySecretary(BookAppointmentBySecretaryRequest $request)
    {
        $validated = $request->validated();
        $doctor = DoctorProfile::find($validated['doctor_id']);
        $start_at = Carbon::parse($validated['start_at']);
        $end_at = $start_at->copy()->addMinutes(15);
        $date = $start_at->toDateString();
        $availableSlots = $this->getAvailableSlots($doctor, $date);
        if (!$availableSlots->contains($start_at->format('H:i'))) {
            return response()->json(['message' => 'Appointment not available'], 409);
        }
        $validated['end_at'] = $end_at;
        $validated['made_by'] = AppointmentMadeBy::Secretary->value;
        $validated['secretary_id'] = auth()->user()->secretary->id;
        $validated['price'] = $doctor->consultation_fee;
        $validated['status'] = AppointmentStatus::Booked->value;
        Appointment::create($validated);
        return response()->json(['message' => 'Appointment Booked']);
    }


    public function availableSlots(AvailableSlotsRequest $request)
    {
        $validated = $request->validated();
        $doctor = DoctorProfile::findOrFail($validated['doctor_id']);
        $availableSlots = $this->getAvailableSlots($doctor, $validated['date']);
        return response()->json($availableSlots);

    }
>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55
}
