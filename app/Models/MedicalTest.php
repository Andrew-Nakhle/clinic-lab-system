<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // 💡 تم إضافة الـ Import المفقود
use Illuminate\Database\Eloquent\Model;

class MedicalTest extends Model
{
    use HasFactory;

    protected $table = 'medical_tests';

    protected $fillable = [
        'name',
        'code',
        'price',
        'display_name',
        'normal_range'
    ];

    public function labRequests()
    {
        return $this->belongsToMany(LabRequest::class, 'lab_request_items')
            ->withPivot('result_value')
            ->withTimestamps();
    }
}
