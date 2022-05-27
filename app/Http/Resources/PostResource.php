<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            //"user" => new UserResource($this->user),
            "title" => $this->title,
            "slug" => $this->slug,
            "body" => $this->body,
            "post_images" => $this->images,
            "created_dates" => [
                "created_at_human" => $this->created_at->diffForHumans(),
                "created_at" => $this->created_at
            ],
            "update_dates" => [
                "updated_at_human" => $this->updated_at->diffForHumans(),
                "updated_at" => $this->updated_at
            ]
        ];
    }
}
