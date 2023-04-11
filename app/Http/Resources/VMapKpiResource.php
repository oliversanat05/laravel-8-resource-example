<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VMapValueResource;
use App\Services\VMapSystem;
use App\Http\Resources\VMapStrategyResource;
use App\Services\VMapHelperServices\VMapHelpers;

class VMapKpiResource extends JsonResource
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
            'id' => $this->kpiId,
            'name' => $this->kpiName,
            'status' => $this->statusId,
            'title' => $this->name,
            'type' => 'level2',
            'parent' => $this->valueId,
            'strategy' => VMapStrategyResource::collection($this->whenLoaded('strategy'))
        ];
    }
}
