<?php

namespace App\Services\ExportCsv;

use App\Models\Succession\VMap;
use App\Services\VMapHelperServices\VMapHelpers;
use App\Services\VMapSystem;
use Auth;

class ExportCsvService
{

    private $helper;
    private $delegates;

    public function __construct()
    {
        $this->helper = new VMapHelpers();
        $this->delegates = new VMapSystem();
    }

    /**
     * for creating the vmap csv
     *
     * @param $data
     * @return void
     */
    public function exportCsvData($data)
    {

        $type = $data['type'];
        $vmapId = $data['vmapId'];

        if ($type == 6) {

            $vmap = $this->helper->getVmapContent($vmapId)->first()->toArray();

            $vmapData = [];

            foreach ($vmap['values'] as $key => $value) {
                $vmapData[$key]['valueId'] = $value['valueId'];
                $vmapData[$key]['valueTitle'] = $value['valueTitle'];
                $vmapData[$key]['completedDate'] = $value['completedDate'];

                foreach ($value['kpis'] as $kpiKey => $kpi) {
                    $vmapData[$key]['kpiId'] = $kpi['kpiId'];
                    $vmapData[$key]['kpiName'] = $kpi['kpiName'];
                    $vmapData[$key]['kDelegateTo'] = $this->getUserDelegates($kpi['delegateTo']);
                    $vmapData[$key]['kAssignDate'] = $kpi['assignDate'];
                    $vmapData[$key]['kDueDate'] = $kpi['dueDate'];
                    $vmapData[$key]['kCompletedDate'] = $kpi['completedDate'];
                    $vmapData[$key]['kStatusId'] = $this->helper->formatStatus($kpi['statusId']);
                    $vmapData[$key]['kShowOnDashboard'] = $kpi['showOnDashboard'] ? 'Y' : 'N';
                    $vmapData[$key]['kIncludeInReporting'] = $kpi['includeInReporting'] ? 'Y' : 'N';
                    $vmapData[$key]['kIncludeInAvatar'] = $kpi['includeInAvatar'] ? 'Y' : 'N';
                    $vmapData[$key]['kIncludeInProfile'] = $kpi['includeInProfile'] ? 'Y' : 'N';
                    $vmapData[$key]['kTrackSign'] = $kpi['trackSign'] ?? '';

                    if (!empty($kpi['trackSign']) && trim($kpi['trackSign']) == '@') {
                        $vmapData[$key]['kGoal'] = $kpi['seasonalGoal'] ? 'values: ' . $kpi['seasonalGoal'] : '';
                    } else if (!empty($kpi['trackSign']) && trim($kpi['trackSign']) == '...') {
                        $vmapData[$key]['kGoal'] = $kpi['successScale'] ?? '';
                    } else {
                        $vmapData[$key]['kGoal'] = $kpi['goal'] ?? '';
                    }

                    $vmapData[$key]['kAccumulate'] = $kpi['isAccumulate'] ? 'Y' : 'N';

                    foreach ($kpi['strategy'] as $strategyKey => $strategy) {
                        $vmapData[$key]['strategyId'] = $strategy['strategyId'];
                        $vmapData[$key]['strategyName'] = $strategy['strategyName'];
                        $vmapData[$key]['sDelegateTo'] = $this->getUserDelegates($strategy['delegateTo']);
                        $vmapData[$key]['sAssignDate'] = $strategy['assignDate'];
                        $vmapData[$key]['sDueDate'] = $strategy['dueDate'];
                        $vmapData[$key]['sCompletedDate'] = $strategy['completedDate'];
                        $vmapData[$key]['sStatusId'] = $this->helper->formatStatus($strategy['statusId']);
                        $vmapData[$key]['sShowOnDashboard'] = $strategy['showOnDashboard'] ? 'Y' : 'N';
                        $vmapData[$key]['sIncludeInReporting'] = $strategy['includeInReporting'] ? 'Y' : 'N';
                        $vmapData[$key]['sIncludeInAvatar'] = $strategy['includeInAvatar'] ? 'Y' : 'N';
                        $vmapData[$key]['sIncludeInProfile'] = $strategy['includeInProfile'] ? 'Y' : 'N';
                        $vmapData[$key]['sTrackSign'] = $strategy['trackSign'] ?? '';

                        if (!empty($strategy['trackSign']) && trim($strategy['trackSign']) == '@') {
                            $vmapData[$key]['sGoal'] = $strategy['seasonalGoal'] ? 'values: ' . $strategy['seasonalGoal'] : '';
                        } else if (!empty($strategy['trackSign']) && trim($strategy['trackSign']) == '...') {
                            $vmapData[$key]['sGoal'] = $strategy['successScale'] ?? '';
                        } else {
                            $vmapData[$key]['sGoal'] = $strategy['goal'] ?? '';
                        }

                        $vmapData[$key]['sAccumulate'] = $strategy['isAccumulate'] ? 'Y' : 'N';

                        foreach ($strategy['project'] as $project) {
                            $vmapData[$key]['projectId'] = $project['projectId'];
                            $vmapData[$key]['projectName'] = $project['projectName'];
                            $vmapData[$key]['pDelegateTo'] = $this->getUserDelegates($project['delegateTo']);
                            $vmapData[$key]['pAssignDate'] = $project['assignDate'];
                            $vmapData[$key]['pDueDate'] = $project['dueDate'];
                            $vmapData[$key]['pCompletedDate'] = $project['completedDate'];
                            $vmapData[$key]['pStatusId'] = $this->helper->formatStatus($project['statusId']);
                            $vmapData[$key]['pShowOnDashboard'] = $project['showOnDashboard'] ? 'Y' : 'N';
                            $vmapData[$key]['pIncludeInReporting'] = $project['includeInReporting'] ? 'Y' : 'N';
                            $vmapData[$key]['pIncludeInAvatar'] = $project['includeInAvatar'] ? 'Y' : 'N';
                            $vmapData[$key]['pIncludeInProfile'] = $project['includeInProfile'] ? 'Y' : 'N';
                            $vmapData[$key]['pTrackSign'] = $project['trackSign'] ?? '';

                            if (!empty($project['trackSign']) && trim($project['trackSign']) == '@') {
                                $vmapData[$key]['pGoal'] = $project['seasonalGoal'] ? 'values: ' . $project['seasonalGoal'] : '';
                            } else if (!empty($project['trackSign']) && trim($project['trackSign']) == '...') {
                                $vmapData[$key]['pGoal'] = $project['successScale'] ?? '';
                            } else {
                                $vmapData[$key]['pGoal'] = $project['goal'] ?? '';
                            }

                            $vmapData[$key]['pAccumulate'] = $project['isAccumulate'] ? 'Y' : 'N';

                            foreach ($project['critical_activity'] as $criticalActivity) {
                                $vmapData[$key]['criticalActivityId'] = $criticalActivity['criticalActivityId'];
                                $vmapData[$key]['criticalActivityName'] = $criticalActivity['criticalActivityName'];
                                $vmapData[$key]['cDelegateTo'] = $this->getUserDelegates($criticalActivity['delegateTo']);
                                $vmapData[$key]['cAssignDate'] = $criticalActivity['assignDate'];
                                $vmapData[$key]['cDueDate'] = $criticalActivity['dueDate'];
                                $vmapData[$key]['cCompletedDate'] = $criticalActivity['completedDate'];
                                $vmapData[$key]['cStatusId'] = $this->helper->formatStatus($criticalActivity['statusId']);
                                $vmapData[$key]['cShowOnDashboard'] = $criticalActivity['showOnDashboard'] ? 'Y' : 'N';
                                $vmapData[$key]['cIncludeInReporting'] = $criticalActivity['includeInReporting'] ? 'Y' : 'N';
                                $vmapData[$key]['cIncludeInAvatar'] = $criticalActivity['includeInAvatar'] ? 'Y' : 'N';
                                $vmapData[$key]['cIncludeInProfile'] = $criticalActivity['includeInProfile'] ? 'Y' : 'N';
                                $vmapData[$key]['cTrackSign'] = $criticalActivity['trackSign'] ?? '';

                                if (!empty($criticalActivity['trackSign']) && trim($criticalActivity['trackSign']) == '@') {
                                    $vmapData[$key]['cGoal'] = $criticalActivity['seasonalGoal'] ? 'values: ' . $criticalActivity['seasonalGoal'] : '';
                                } else if (!empty($criticalActivity['trackSign']) && trim($criticalActivity['trackSign']) == '...') {
                                    $vmapData[$key]['cGoal'] = $criticalActivity['successScale'] ?? '';
                                } else {
                                    $vmapData[$key]['cGoal'] = $criticalActivity['goal'] ?? '';
                                }

                                $vmapData[$key]['cAccumulate'] = $criticalActivity['isAccumulate'] ? 'Y' : 'N';
                            }
                        }

                    }
                }

            }

            return collect($vmapData);
        }
    }

    /**
     * removing unnecessary data from the delegate data response
     *
     * @return array
     */
    public function formatDelegateData()
    {
        $delegates = $this->delegates->getActiveDelegate()->toArray();
        $delegateData = [];
        foreach ($delegates as $key => $delegate) {
            $delegateData[$delegate['userId']] = $delegate['user']['name'];
        }

        return $delegateData;
    }

    /**
     * implode and explode the delegate ids
     *
     * @return void
     */
    public function getUserDelegates($delegateId)
    {
        $delegateIds = [];

        $delegates = $this->formatDelegateData();
        // dd($delegates);

        if ($delegateId != '' || !empty($delegateId)) {
            $delegateArray = explode(',', $delegateId);
            foreach ($delegateArray as $delegate) {
                if (isset($delegates[$delegate])) {
                    $delegateIds[] = $delegates[$delegate];
                }

            }
        } else {
            $delegateIds[] = $delegates[Auth::user()->user_id];
        }
        return ($delegateIds) ? implode(',', $delegateIds) : '';
    }

}
