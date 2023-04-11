<?php

namespace App\Http\Controllers\Api\V1\VMap;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\VMapHelperServices\VMapHelpers;
use App\Services\VMapActionServices\VMapActionService;
use App\Http\Requests\vMapActionRequest\VMapCopyRequest;
use App\Services\VMapActionServices\VMapCopyLevelService;

class VMapCopyController extends Controller
{

    use ApiResponse;

    public function __construct()
    {
        $this->vMapAction = new VMapActionService();
        $this->helper = new VMapHelpers();
    }

    /**
     * This function will create the copy of the vmap
     * @param VMapCopyRequest $request
     * @return response JSON
     */
    public function store(VMapCopyRequest $request, $vMapId)
    {
        $vmap = $this->vMapAction->makeVMapCopy($request->all(), $vMapId);

        $copiedVMap = $this->helper->getVmapContent($vmap)->first();
        if ($vmap) {
            return $this->successApiResponse(__('core.vMapCopySuccess'), $copiedVMap);
        } else {
            return $this->unprocessableApiResponse(__('core.vMapCopyError'));
        }
    }
}
