<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretaryResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [

            'profile_image_url' => $this->profile_image ? asset('storage/' . $this->profile_image) : null,
             'user' => $this->whenLoaded('user'),
              ];
    }
}
