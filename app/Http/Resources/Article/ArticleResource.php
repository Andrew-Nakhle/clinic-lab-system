<?php

namespace App\Http\Resources\Article;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'doctor_id'=>$this->doctor_id,
            'title'=>$this->title,
            'content'=>$this->content,
            'category'=>$this->category,
            'image'=>$this->image ? url('storage/'.$this->image) : null,

        ];
    }
}
