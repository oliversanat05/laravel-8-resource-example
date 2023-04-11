<?php

namespace App\Http\Controllers\Api\V1\VMap;

use App\Http\Controllers\Controller;
use App\Services\VMapFilterService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class VMapController extends Controller
{

    use ApiResponse;

    private $vmapFilterService;

    public function __construct()
    {
        $this->vmapFilterService = new VMapFilterService();
    }

    /**
     * for getting the vmap data
     *
     * @param Request $request
     * @return json
     */
    public function values(Request $request)
    {
        $vmapId = $request->query('vmapId');

        $vmapData = $this->vmapFilterService->getVmapValues(intval($vmapId));

        return $this->successApiResponseWithoutMessage($vmapData);
    }

    /**
     * get all the kpis related to the values
     *
     * @param Request $request
     * @return json
     */
    public function kpis(Request $request)
    {
        $valueId = $request->query('valueId');

        $getKpis = $this->vmapFilterService->getValueKpis($valueId);

        return $this->successApiResponseWithoutMessage($getKpis);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return json
     */
    public function strategies(Request $request)
    {
        $kpiId = $request->query('kpiId');

        $getStrategies = $this->vmapFilterService->getKpiStrategies($kpiId);
        return $this->successApiResponseWithoutMessage($getStrategies);
    }

    /**
     * get all the level 4 related to level 3
     *
     * @param Request $request
     * @return json
     */
    public function projects(Request $request)
    {
        $strategyIds = $request->query('strategyId');

        $getProjects = $this->vmapFilterService->getStrategyProjects($strategyIds);
        return $this->successApiResponseWithoutMessage($getProjects);
    }

    /**
     * get all level 5 related to level 4
     *
     * @param Request $request
     * @return json
     */
    public function criticalActivities(Request $request)
    {
        $getProjectIds = $request->query('projectId');

        $getCriticalActivities = $this->vmapFilterService->getProjectCriticalActivities($getProjectIds);
        return $this->successApiResponseWithoutMessage($getCriticalActivities);
    }

    /**
     * for getting the vmap data
     *
     * @param Request $request
     * @return json
     */
    public function getValues(Request $request)
    {
        $vmapId = $request->get('data');

        $vmapData = $this->vmapFilterService->getAllValues($vmapId);

        return $this->successApiResponseWithoutMessage($vmapData);
    }

    /**
     * for getting the vmap data
     *
     * @param Request $request
     * @return json
     */
    public function getKpis(Request $request)
    {
        $valueId = $request->get('data');

        $vmapData = $this->vmapFilterService->getValueKpis($valueId);

        return $this->successApiResponseWithoutMessage($vmapData);
    }

    /**
     * for getting the vmap data
     *
     * @param Request $request
     * @return json
     */
    public function getActivityDetails($level, $id)
    {
        $activityData   =[];
        switch ($level) 
        {
            case 'level2':
                $activityData = $this->vmapFilterService->getKpiDetails($id);
                break;
            
            case 'level3':
                $activityData = $this->vmapFilterService->getStrategyDetails($id);
                break;

            case 'level4':
                $activityData = $this->vmapFilterService->getProjectDetails($id);
                break;

            case 'level5':
                $activityData = $this->vmapFilterService->getCriticalActivityDetails($id);
                break;
            default:
                # code...
                break;
        }

        return $this->successApiResponseWithoutMessage($activityData);
    }
}
