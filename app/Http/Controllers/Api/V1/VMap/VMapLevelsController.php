<?php

namespace App\Http\Controllers\Api\V1\VMap;

use DB;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\FilterSystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\VMap\VMapRemainingLevelsRequest;
use App\Services\VMapActionServices\VMapActionService;
use App\Http\Requests\vmapLevelRequest\VmapValueRequest;
use App\Http\Requests\vMapActionRequest\VMapLevelsRequest;

class VMapLevelsController extends Controller
{

    use ApiResponse;

    public function __construct(){
        $this->vMapAction = new VMapActionService();
        $this->filter = new FilterSystem();
    }
    /**
     * This function will update the
     * vMap levels
     *
     * @param VMapLevelsRequest $request
     * @return json
     */
	public function update(VMapLevelsRequest $request, $levelId, $type) {
        try {

            DB::beginTransaction();
            $dataArray = $this->vMapAction->vMapActivityUpdate($request->all(), $levelId, $type);
            DB::commit();
            if (isset($dataArray)) {
                return $this->successApiResponse('level updated successfully', $dataArray);
            } else {
                return $this->unprocessableApiResponse(__('core.vMapActivityUpdateError'));
            }
        } catch (\Throwable $th) {

            DB::rollback();
            return $this->unprocessableApiResponse($th->getMessage());
        }
        return null;
	}

    /**
     * This function will delete the vMap levels
     * @param Request $request
     * @return JSON
     */
    public function destroy(Request $request, $id, $type)
    {
        $dataArray  = [];
        $msgArray = [];
        try {
            $dataArray['isDelete']  = ($type == 'level1') ? false : true;
            $response = $this->vMapAction->getDeleteActivity($dataArray, $id, $type);
            if($response){
                if($request->title)
                    $this->filter->setDeletedItems($request['title'], $id, $type);
                return $this->successApiResponse(
                    __('core.actDeleted')
                );
                }else{
                    return $this->unprocessableApiResponse(__('core.actDeletedError'));
                }
        } catch (\Exception $th) {
            return $this->unprocessableApiResponse(__('core.actDeletedError'));
        }
    }

    /**
     * for saving the vmap levels to the database starting from level 2
     *
     * @param Request $request
     * @param [type] $levelId   is the parent id of the level
     * @param [type] $type      is the type of the level i.e., level2 for kpi, level3 for strategy and so on.
     * @return void
     */
    public function store(VMapRemainingLevelsRequest $request, $levelId, $type)
    {
        $response = $this->vMapAction->createVmapLevels($request->all(), $levelId, $type);

        return $response;
    }


    /**
     * for storing the level 1 of the vmap
     *
     * @param VmapValueRequest $request
     * @return void
     */
    public function createValueLevel(VmapValueRequest $request, $id)
    {
        try {

            $response = $this->vMapAction->createValues($request->all(), $id);

            if($response) {
                return $this->successApiResponse(__('core.valueSuccess'), $response);
            } else {
                return $this->unprocessableApiResponse(__('core.vmapIdNotFound'));
            }
        } catch (\Throwable $th) {
            return $this->errorApiResponse($th->getMessage());
        }
    }
}
