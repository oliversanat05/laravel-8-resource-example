<?php

namespace App\Http\Resources\ActivityTitle;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityTitleResource extends JsonResource
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
            'id' => $this->ID,
            'vmapId' => $this->vmvId,
            'value' => $this->valueTitle,
            'kpi' => $this->kpiTitle,
            'strategy' => $this->strategyTitle,
            'project' => $this->projectTitle,
            'criticalActivity' => $this->caTitle,
            'kpiCheck' => $this->kActivityCheck,
            'strategyCheck' => $this->sActivityCheck,
            'projectCheck' => $this->pActivityCheck,
            'criticalActivityCheck' => $this->cActivityCheck,
        ];
    }
}
