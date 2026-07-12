<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles,HasPermissions ,HasApiTokens;
    //protected $guard_name = 'web';
    protected $guard_name = 'api';
    protected $casts = [
        'status' => UserStatus::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable = [
            'first_name',
            'last_name',
            'phone',
            'email',
            'password',
            'gender',
            'birth_date',
            'otp_code',

        ];
    public  function generateOtpCode(){
        $this->otp_code=rand(100000,999999);
        $this->otp_expires_at=now()->addMinutes(10);
        $this->timestamps=false;
        $this->save();
    }

    public function doctor(){
        return $this->hasOne(DoctorProfile::class);
    }
     public function secretary()
     {
         return $this->hasOne(SecretaryProfile::class);
     }
     public function patient()
     {
         return $this->hasOne(PatientProfile::class);
     }
//    public function doctorAppointments()
//    {
//        return $this->hasMany(Appointment::class);
//    }

//    public function patientAppointments()
//    {
//        return $this->hasMany(Appointment::class);
//    }

//    public function secretaryAppointments()
//    {
//        return $this->hasMany(Appointment::class);
//    }
    public function payments()
    {
return $this->hasMany(Payment::class);
    }



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
