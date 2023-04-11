<?php

namespace App\Http\Controllers\Api\V1\VMap;

use Lang;
use Config;
use Validator;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\FilterSystem;
use App\Models\Succession\VMap;
use App\Models\Succession\Value;
use App\Http\Controllers\Controller;
use App\Http\Resources\VMapResource;
use App\Http\Resources\VMapCollection;
use App\Services\VMapHelperServices\VMapHelpers;
use App\Services\VMapActionServices\VMapActionService;
use App\Http\Requests\vMapActionRequest\VMapCopyRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\vMapActionRequest\VMapActionRequest;
use App\Http\Requests\vMapActionRequest\VMapLevelsRequest;
use App\Http\Requests\vMapActionRequest\VMapUpdateRequest;

class VMapActionController extends Controller {

    use ApiResponse;

	private $vMapAction;
    private $filter;

	public function __construct() {
		$this->vMapAction = new VMapActionService();
        $this->filter = new FilterSystem();
        $this->helper = new VMapHelpers();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(VMapActionRequest $request) {
        try {

            $dataArray = $this->vMapAction->createNewVMap($request->vMapData());

            $response = $this->helper->getVmapContent($dataArray['vMapId'])->first();
            return $this->successApiResponse(__('core.vMapSuccess'), $response);

        } catch (\Throwable $th) {
            return $this->errorApiResponse(__('core.vMapRollback'));
        }
	}

    /**
	 * udpate the vmap.
	 *
	 * @param  \Illuminate\Http\Request  $request, $id
	 * @return \Illuminate\Http\Response
	 */
    public function update(VMapUpdateRequest $request, $id)
    {
        try {
            $updateVmap = $this->vMapAction->updateNewVMap($request->all(), $id);
            $response = $this->helper->getVmapContent($updateVmap['vMapId'])->first();
            return $this->successApiResponse(__('core.vMapUpdate'), $response);
        } catch (\Throwable $th) {
            return $this->errorApiResponse(__('core.internalServerError'));
        }

    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param NA
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, $id) {
        try {
            $response = $this->vMapAction->deleteVMap($id);
            return $response;
        } catch (\Throwable $th) {
            dd($th);
            return $this->errorApiResponse(__('core.internalServerError'));
        }
        return null;
	}

    /**
     * this function will get the particular vmap data according to the vmap id
     *
     * @param $id
     * @return collection
     */
    public function show($id)
    {
        $vmap = $this->helper->getVmapContent($id)->first();
        $vmapResource = VMapResource::make($vmap);
        return $this->successApiResponse(__('core.vmapFilterFetched'), $vmapResource);
    }
}
