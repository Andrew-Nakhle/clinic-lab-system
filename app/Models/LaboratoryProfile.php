<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoryProfile extends Model
{
    protected $table = 'laboratory_profiles';

    protected $fillable = [
        'user_id',
        'section_id',
        'image',
        'license_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
