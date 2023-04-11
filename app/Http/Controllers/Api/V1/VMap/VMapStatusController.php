<?php

namespace App\Http\Controllers\Api\V1\VMap;

use App\Http\Controllers\Controller;
use App\Services\VMapActionServices\VMapActionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class VMapStatusController extends Controller
{

    use ApiResponse;

    public function __construct()
    {
        $this->vMapAction = new VMapActionService();
    }

    /**
     * This function will update the vmap status
     * @param Request $request
     * @return JSON
     */
    public function update(Request $request, $id, $type)
    {
        try {
            $response = $this->vMapAction->statusUpdate($request->status, $id, $type);
            if ($response) {
                return $this->successApiResponse(__('core.statusUpdated'));
            } else {
                return $this->unprocessableApiResponse(__('core.statusUpdatedError'));
            }
        } catch (\Throwable$th) {
            return $this->errorApiResponse(__('core.levelNotExists'));
        }
    }

}
