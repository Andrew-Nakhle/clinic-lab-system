<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = ['medicine_name','instructions','prescription_id'];
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
