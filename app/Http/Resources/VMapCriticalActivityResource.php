<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\VMapSystem;
use App\Services\VMapHelperServices\VMapHelpers;

class VMapCriticalActivityResource extends JsonResource
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
            'id' => $this->criticalActivityId,
            'name' => $this->criticalActivityName,
            'status' => $this->statusId,
            'title' => $this->criticalActivityName,
            'type' => 'level5',
            'parent' => $this->projectId,
        ];
    }
}
