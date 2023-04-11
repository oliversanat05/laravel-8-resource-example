<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VMapKpiResource;

class VMapValueResource extends JsonResource
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
            'id' => $this->valueId,
            'name' => $this->valueTitle,
            'title' => $this->valueTitle,
            'url' => $this->valueUrl,
            'status' => $this->statusId,
            'vMapId' => $this->vMapId,
            'type' => 'level1',
            'kpis' => VMapKpiResource::collection($this->whenLoaded('kpis'))
        ];
    }
}
