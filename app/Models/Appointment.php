<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public function doctor(){
         return $this->belongsTo(User::class);
    }
    public function patient(){
        return $this->belongsTo(User::class);
    }
    public function secretary(){
        return $this->belongsTo(User::class);
    }


}
