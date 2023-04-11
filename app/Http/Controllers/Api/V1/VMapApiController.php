<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeletedItem;
use App\Models\Succession\VMap;
use App\Services\FilterSystem;
use App\Services\VMapHelperServices\VMapHelpers;
use App\Services\VMapSystem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class VMapApiController extends Controller
{
    use ApiResponse;

    private $vMap;
    private $vMapHelper;
    public function __construct()
    {
        $this->vMap = new VMapSystem();
        $this->filter = new FilterSystem();
        $this->vMapHelper = new VMapHelpers();
    }

    /**
     * This function will get the vmaps list,
     * delegate list, users profile data
     *
     * @param Request $request
     * @return json array
     */
    public function index(Request $request)
    {
        try {

            $include = $request->query('include'); //to pass relationship in parameters

            $pageSize = $request->query('pageSize') ?? 10000;

            $include = explode('.', $include);

            $vmaps = $this->vMap->vMapContents($include, $pageSize);

            $vmapArray = [];

            foreach ($vmaps as $key => $vmap) {
                // dd($vmap);
                $vmapArray[$key]['vmap']['id'] = $vmap['vMapId'];
                $vmapArray[$key]['vmap']['name'] = $vmap['formTitle'];
                $vmapArray[$key]['vmap']['date'] = $vmap['formDate'];
                $vmapArray[$key]['vmap']['vission'] = $vmap['visionStatement'];

                $vmapArray[$key]['vmap']['mission'] = $vmap['missionStatement'];

                foreach ($vmap['values'] as $valueKey => $value) {
                    // dd($value);
                    $vmapArray[$key]['vmap']['values'][$valueKey]['id'] = $value['valueId'];
                    $vmapArray[$key]['vmap']['values'][$valueKey]['name'] = $value['valueTitle'];
                    $vmapArray[$key]['vmap']['values'][$valueKey]['url'] = $value['valueUrl'];
                    $vmapArray[$key]['vmap']['values'][$valueKey]['status'] = $value['statusId'];
                    $vmapArray[$key]['vmap']['values'][$valueKey]['vmapId'] = $value['vMapId'];
                    $vmapArray[$key]['vmap']['values'][$valueKey]['type'] = 'level1';

                    foreach($value['kpis'] as $kpiKey => $kpi) {
                        $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['id'] = $kpi['kpiId'];
                        $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['name'] = $kpi['kpiName'];
                        $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['status'] = $kpi['statusId'];
                        $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['type'] = 'level2';
                        $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['parent'] = $kpi['valueId'];

                        foreach($kpi['strategy'] as $strategyKey => $strategy) {
                            $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['id'] = $strategy['strategyId'];
                            $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['name'] = $strategy['strategyName'];
                            $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['status'] = $strategy['statusId'];
                            $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['type'] = 'level3';
                            $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['parent'] = $strategy['kpiId'];

                            foreach($strategy['project'] as $projectKey => $project) {
                                $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['id'] = $project['projectId'];
                                $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['name'] = $project['projectName'];
                                $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['status'] = $project['statusId'];
                                $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['level'] = 'level4';
                                $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['parent'] = $project['strategyId'];

                                foreach($project['critical_activity'] as $caKey => $criticalActivity) {
                                    $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['critical_activities'][$caKey]['id'] = $criticalActivity['criticalActivityId'];
                                    $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['critical_activities'][$caKey]['name'] = $criticalActivity['criticalActivityName'];
                                    $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['critical_activities'][$caKey]['status'] = $criticalActivity['statusId'];
                                    $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['critical_activities'][$caKey]['level'] = 'level5';
                                    $vmapArray[$key]['vmap']['values'][$valueKey]['kpis'][$kpiKey]['strategies'][$strategyKey]['projects'][$projectKey]['critical_activities'][$caKey]['parent'] = $criticalActivity['projectId'];
                                }

                            }

                        }

                    }
                }

            }

            return $vmapArray;

            // return $this->successApiResponse(__('core.vmap'), VMapResource::collection($vmaps));

        } catch (\Throwable$th) {
            return $this->errorApiResponse(
                $th->getMessage()
            );
        }

    }

    /**
     * get all the qualifier list based on the logged in user
     * @param NA
     * @return qualifier JSON
     *
     */
    public function getQualifierList()
    {
        $data = $this->vMapHelper->delegatedUsers($this->vMap->getUserQualifier());
        return $this->successApiResponse(__('core.qualifierList'), $data);
    }

    /**
     *
     * this function will get the deleted vmaps
     */

    public function deletedVMapsList()
    {
        $response = $this->filter->getAllDeletedVmaps();
        if (count($response)) {
            return $this->successApiResponse(__('core.deleteVmapList'), $response);
        } else {
            return $this->successApiResponse(
                __('core.emptyResponse')
            );
        }
    }

    /**
     * this function restore the deleted vmaps or its child => values, kpis, strategy etc
     * @param Request
     * @return json
     */
    public function restore(Request $request, $type, $id)
    {

        try {
            $check_table_id = DeletedItem::whereItemid($id)->exists();

            $response = $this->filter->undoVMap($type, $id);

            if ($check_table_id) {
                $this->filter->getDeleteItems((int) $id);

                return $this->successApiResponse(
                    __('core.redoActivity')
                );
            } else {
                return $this->unprocessableApiResponse(__('core.redoActivityError'));
            }
        } catch (\Throwable$th) {
            return $this->unprocessableApiResponse(__('core.redoActivityError'));
        }
    }

    /**
     * only in development mode
     *
     * @param Request $request
     * @return void
     */
    public function getVmapData(Request $request)
    {
        try {

            $include = $request->query('include'); //to pass relationship in parameters

            $pageSize = $request->query('pageSize');

            $include = explode('.', $include);

            $vmaps = $this->vMap->vMapContents($include, $pageSize);

            return $this->successApiResponse(__('core.vmap'), $vmaps);

        } catch (\Throwable$th) {
            dd($th->getMessage());
            return $this->errorApiResponse(
                __('core.internalServerError')
            );
        }

    }
}
