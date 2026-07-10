<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecretaryProfile extends Model
{
    protected $table= 'secretary_profiles';
    protected $fillable= ['user_id', 'section_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function section(){
return $this->belongsTo(Section::class);
    }
    public function secretaryAppointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
