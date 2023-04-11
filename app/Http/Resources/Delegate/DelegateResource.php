<?php

namespace App\Http\Resources\Delegate;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Communication\CommunicationResource;

class DelegateResource extends JsonResource
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
            'id' => $this->delegateUsersId,
            'userId' => $this->userId,
            'parentId' => $this->parentId,
            'isDelegate' => $this->isDelegate,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'communication' => new CommunicationResource($this->whenLoaded('communication'))
        ];
    }
}
