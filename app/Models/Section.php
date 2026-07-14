<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{

    protected $table = 'sections';
    protected $fillable = ['base_price'];
    public function secretary(){
        return $this->hasOne(SecretaryProfile::class);
    }
    public function doctors()
    {
        return $this->hasMany(DoctorProfile::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

}
