<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\VMapSystem;
use App\Http\Resources\VMapCriticalActivityResource;
use App\Services\VMapHelperServices\VMapHelpers;

class VMapProjectResource extends JsonResource
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
            'id' => $this->projectId,
            'name' => $this->projectName,
            'status' => $this->statusId,
            'title' => $this->projectName,
            'type' => 'level4',
            'parent' => $this->strategyId,
            'criticalActivity' => VMapCriticalActivityResource::collection($this->whenLoaded('criticalActivity'))
        ];
    }
}
