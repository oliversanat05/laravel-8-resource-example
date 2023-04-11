<?php

namespace App\Http\Resources;

use Config;
use Carbon\Carbon;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\VMapLevel1Resource;
use Illuminate\Http\Resources\Json\JsonResource;

class VMapResource extends JsonResource
{

    // public static $wrap = 'vmaps';
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'id' => $this->vMapId,
            'name' => $this->formTitle,
            'title' => $this->formTitle,
            'subTitle' => $this->formTitle,
            'formDate' => Carbon::parse($this->formDate)->format(Config::get('constants.dateFormat')),
            'vision' => $this->visionStatement,
            'mission' => $this->missionStatement,
            'isDelete' => $this->isDelete,
            'showOnDashboard' => $this->showOnDashboard,
            'values' => isset($this->values) ?  VMapValueResource::collection($this->whenLoaded('values')) : null,
            'activityTitle' => $this->activityTitle,

        ];
    }
}
