<?php

namespace App\Http\Resources\Coach;

use Illuminate\Http\Resources\Json\JsonResource;

class CoachResource extends JsonResource
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
            'id' => $this->coachEventId,
            'userName' => $this->userName,
            'vmapPage' => $this->vmap,
            'callMaximizerPage' => $this->callmaximizer,
            'dashboardPage' => $this->dashboard,
            'trackingDataPage' => $this->trackingData,
            'coachPathPage' => $this->coachPath,
            'coachName' => $this->coachUserName,
            'transDate' => $this->transDate,
            'userId' => $this->userId,
            'startHere' => $this->startHere,
        ];
    }
}
