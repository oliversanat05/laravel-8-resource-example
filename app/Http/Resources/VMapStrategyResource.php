<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\VMapSystem;
use App\Http\Resources\VMapStrategyResource;
use App\Services\VMapHelperServices\VMapHelpers;

class VMapStrategyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $level2 = $this->kpi->kpiId;

        return [
            'id' => $this->strategyId,
            'name' => $this->strategyName,
            'status' => $this->statusId,
            'title' => $this->strategyName,
            'type' => 'level3',
            'parent' => $this->kpiId,
            'project' => VMapProjectResource::collection($this->whenLoaded('project'))
        ];
    }
}
