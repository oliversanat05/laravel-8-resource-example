<?php

namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponse;
use App\Services\VMapSystem;
use Illuminate\Http\Request;
use App\Models\ActivityTitle;
use App\Http\Controllers\Controller;
use App\Services\ActivityTypeTitleList;
use App\Http\Resources\ActivityTitle\ActivityTitleResource;
use App\Http\Resources\ActivityTitle\ActivityTitleCollection;
use App\Http\Requests\vmapActivityTitleRequest\ActivityTitleRequest;

class ActivityTitleListController extends Controller
{

    use ApiResponse;

    public function __construct()
    {
        $this->activity = new ActivityTypeTitleList();
    }

    /**
     * method used to get the activity list which is associated with the user and it's vmap id.
     */
    public function index(Request $request){
        try{
            $vMap = new VMapSystem;
            $pageSize = $request->query('pageSize');
            $vMapIds = $vMap->vMapList()->pluck('vMapId')->toArray();
            $activityTitle = new ActivityTypeTitleList;
            $activityTypeTitleList = $activityTitle->getActivityTitleList($vMapIds, $pageSize);


            return new ActivityTitleCollection($activityTypeTitleList);
        }catch(\Exception $ex){
            return $this->errorApiResponse(__('core.internalServerError'));
        }
    }

    /**
     * method used to get the activity title which is associated with the vmap id
     */
    public function show($vMapId){
        try{
            $activityTitle = new ActivityTypeTitleList;

            $checkActivityTitleExists = ActivityTitle::whereVmvid($vMapId)->exists();

            $activityTypeTitleList = '';

            if($checkActivityTitleExists){

                $activityTypeTitleList = $activityTitle->getActivityTitleForVmapId($vMapId);
                return $this->successApiResponse(__('core.activityTitle'), new ActivityTitleResource($activityTypeTitleList));
            } else {
                return response()->json([
                    'status' => true,
                    'data' => []
                ]);
            }
        }catch(\Exception $ex){
            return $this->errorApiResponse(__('core.internalServerError'));
        }
    }

    /**
     * This function will update the activity title
     * @param ActivityTitleRequest $request
     */
    public function update(Request $request, $id)
    {
        try {
            $activity = $this->activity->updateActivityTitle($request->all(), $id);

            if($activity){
                return $this->successApiResponse(__('core.activitySuccess'), new ActivityTitleResource($activity));
            }else{
                return $this->unprocessableApiResponse(__('core.activityFailed'));
            }
        } catch (\Throwable $th) {
            return $this->errorApiResponse(__('core.internalServerError'));
        }
    }

    /**
     * This function will delete the
     * activity
     * @param Request $request
     * @return JSON
     */
    public function destroy($id)
    {
        try {
            $checkIdExists = ActivityTitle::where('ID', $id);
            if($checkIdExists->exists()) {
                $activityId = $this->activity->deleteActivityTitle($id);
                if($activityId){
                    return $this->successApiResponse(__('core.deleteActivitySuccess'));
                }else{
                    return $this->unprocessableApiResponse(__('core.deleteActivityError'));
                }
            }else{
                return $this->unprocessableApiResponse(__('core.exists'));
            }

        } catch (\Throwable $th) {
            return $this->errorApiResponse(__('core.internalServerError'));
        }

    }
}
