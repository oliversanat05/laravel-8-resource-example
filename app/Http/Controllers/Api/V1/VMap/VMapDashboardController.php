<?php

namespace App\Http\Controllers\Api\V1\VMap;

use Validator;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\VMapActionServices\VMapActionService;

class VMapDashboardController extends Controller
{

    use ApiResponse;

    public function __construct()
    {
        $this->vMapAction = new VMapActionService();
    }
    /**
     * This function will add or remove the selected vmap
     * from the dashboard
     *
     * @param NA
     * @return response
     */
    public function store(Request $request, $id)
    {
        Validator::make($request->all(), [
            'value' => ['required'],
        ])->validate();
        try {
            $response = $this->vMapAction->addToDashboard($id, $request->value);
            if ($response) {
                return $this->successApiResponse(__('core.activityUpdateSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.activityUpdateError'));
            }
        } catch (\Throwable$th) {
            return $this->unprocessableApiResponse(__('core.activityUpdateError'));
        }
    }
}
