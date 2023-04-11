<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Role\RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id' => $this->user_id,
            'name' => $this->name,
            'roleId' => $this->role_id,
            'userName' => $this->user_name,
            'email' => $this->email,
            'description' => $this->description,
            'userImage' => $this->user_image,
            'defaultProfile' => $this->default_profile,
            'status' => $this->status,
            'dialNumber' => $this->dialNumber,
            'accessCode' => $this->accessCode,
            'meetingLink' => $this->marketingLink,
            'isCompleted' => $this->isCompleted,
            'lastLogin' => $this->lastLoginDate,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'deletedAt' => $this->deleted_at,
            'role' =>  new RoleResource($this->whenLoaded('role')),
            'children' => $this?->children
        ];
    }
}
