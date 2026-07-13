<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PatientProfile extends Model
{
   use HasFactory;
   protected $table = 'patient_profiles';
   protected $fillable = [
       'user_id',
       'blood_group',
       'weight',
       'tall',
       'id_card',
       'profile_image',
       'section_id',
       'medical_record_access_code'
   ];
   public static function generateMedicalAccessCode(){

           do{
       $code=Str::upper(Str::random(6));
           }
           while(self::where('medical_record_access_code',$code)->exists());

       return $code;

   }
   public function user()
   {
       return $this->belongsTo(User::class);
   }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function reports(){
       return $this->hasMany(Report::class,'patient_id');
    }

}
