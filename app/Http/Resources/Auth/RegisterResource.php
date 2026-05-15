<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'first_name'=>$this->first_name,
            'last_name'=>$this->last_name,
            'phone'=>$this->phone,
            'email'=>$this->email,
            'gender'=>$this->gender,
            'birth_date'=>$this->birth_date,
            'profile'=>$this->getProfileData()

        ];
    }
    private function getProfileData()
    {

        if ($this->hasRole('doctor')){
            return [
                'prfile_image'=> url('storage/'.$this->doctor->profile_image),
                'specialization' => $this->doctor->specialization ?? null,
                'experience_years' => $this->doctor->experience_years ?? null,
                'certification' => $this->doctor->certification ? url('storage/'.$this->doctor->certification): null,
                'bio' => $this->bio ?? null,
            ];
        }
        if ($this->hasRole('patient')){
            return [
                'tall'=>$this->patient->tall ,
                'weight'=>$this->patient->weight ,
                'blood_group'=>$this->patient->blood_group,
                'profile_image'=>$this->patient->profile_image ? url('storage/'.$this->patient->profile_image) : null,
                'id_card'=>$this->patient->id_card ? url('storage/'.$this->patient->id_card) : null,

            ];
        }
        if ($this->hasRole('secretary')){
            return[
                'section_id'=>$this->secretary->section_id
            ];
        }
    }
}
