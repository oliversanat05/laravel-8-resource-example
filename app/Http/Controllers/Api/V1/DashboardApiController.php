<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardSystemService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use DB;

class DashboardApiController extends Controller
{

    use ApiResponse;

    public function __construct()
    {
        $this->dashboard = new DashboardSystemService();
    }

    /**
     * this function will get the notification data from the url
     * @param Request $request
     * @return JSON
     */
    public function getUserNotificationData(Request $request, $type, $key)
    {
        // dd($key, $type);
        return $this->dashboard->getArrangedData($this->dashboard->userNotificationData($key, $type));
    }

    /***
     * Create a function to to get the activity avatar API data
     * @param NA
     * @return view
     */
    public function getDashboardDataApi(Request $request)
    {
        try {

            $filters = $request->only([
                'activeProfile',
                'activeVMap',
                'activeValue',
                'activeKpi',
                'activeStrategy',
                'activeProject',
                'activeCriticalActivity',
                'activeDelegate',
                'startDate',
                'activeStatus',
                'endDate']
            );

            $response = $this->dashboard->calUserDataSummery($filters);

            return $this->successApiResponse(__('core.updateTrackingData'), $response);

        } catch (\Throwable$th) {
            return $th->getMessage();
            return $this->errorApiResponse(__('core.internalServerError'));
        }
    }

    /*
     * this function will call the class function to get the query data
     * @param request
     * @return json
     */
    function getSpecificActivity(){

        try {
            $response = $this->dashboard->callTrackingValues();
            return $this->successApiResponse(__('core.updateTrackingData'), $response);
        } catch (\Throwable $th) {
            return $th->getMessage();
            return $this->errorApiResponse(__('core.internalServerError'));
        }
    }
    /**
     * for udpating the level's description from the dashboard
     *
     * @param Request $request
     * @return void
     */
    public function dashboardQuickUpdate(Request $request)
    {

        try {
            DB::beginTransaction();
            $params = $request->data;
            $activity = $this->dashboard->quickUpdate($params);
            // dd($activity);

            DB::commit();

            if ($activity) {
                return $this->successApiResponse(__('core.activityUpdate'));
            } else {
                return $this->unprocessableApiResponse(__('core.activityUpdateError'));
            }
        } catch (\Throwable$th) {

            DB::rollback();
            throw $th;
            return $this->errorApiResponse($th->getMessage());
        }
    }

    /**
     * for calculating the avatar global health in the dashboard
     *
     * @return void
     */
    public function avatarGlobalHealth()
    {
        try {
            $response = $this->dashboard->getDashboardUserAvatarHealth();
            return $response;
        } catch (\Throwable $th) {
            return $this->errorApiResponse($th->getMessage());
        }
    }
}
