<?php

namespace App\Http\Resources\Role;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'id' => $this->userRoleId,
            'role' => $this->description,
            'status' => $this->active,
            'sorting' => $this->sorting,
            'sortOrder' => $this->sortOrder,
            'canLogin' => $this->canLogin,
            'transDate' => $this->transDate,
        ];
    }
}
