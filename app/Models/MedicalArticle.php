<?php

namespace App\Models;

use App\Enums\Article\MedicalArticleCategory;
use Illuminate\Database\Eloquent\Model;

class MedicalArticle extends Model
{
    protected $fillable = [
        'doctor_id',
        'title',
        'content',
        'image',
        'category'
    ];
    protected $casts = [
        'category' => MedicalArticleCategory::class,
    ];

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class);
    }
}
