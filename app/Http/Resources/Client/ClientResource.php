<?php

namespace App\Http\Resources\Client;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'eventId' => $this->eventId,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'vmap' => $this->vmap,
            'callMaximizer' => $this->callmaximizer,
            'dashboard' => $this->dashboard,
            'trackingData' => $this->trackingData,
            'coachPath' => $this->coachPath,
            'login' => $this->login,
            'transDate' => $this->transDate,
            'startHere' => $this->startHere,
            'user' => new UserResource($this->user)
        ];
    }
}
