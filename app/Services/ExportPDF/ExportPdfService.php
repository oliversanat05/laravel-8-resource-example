<?php

namespace App\Services\ExportPDF;

use App\Models\Succession\VMap;
use App\Services\VMapHelperServices\VMapHelpers;
use Auth;

class ExportPDFService
{

    private $helper;

    public function __construct()
    {
        $this->helper = new VMapHelpers();
    }
    /**
     * export PDF
     *
     * @param [type] $data
     * @return void
     */
    public function exportVmapPDF($data)
    {

        $vmapId = $data['vmapId'];
        $type = $data['type'];
        // $displayData = $data['asDisplayedData'];

        $result = [];

        if ($type == 3) {

            $result['checkType'] = $type;
            // $result = $displayData;
            dd($result);
        } else {
            $userId = Auth::user()->user_id;

            if ($type == 5 || $type == 4) {
                $status = [0, 1];
                $isComplete = false;
                $result = $this->getVMapContents($vmapId, $userId, $status, $isComplete);
                $result['checkType'] = $data['type'];
            } else if ($type == 7 || $type == 8) {
                $status = [2];
                $isComplete = false;
                $result = $this->getVMapContents($vmapId, $userId, $status, $isComplete);
                $result['checkType'] = $data['type'];
            } else if ($type == 1 || $type == 2) {
                $status = [0, 1, 2];
                $isComplete = false;
                $result = $this->getVMapContents($vmapId, $userId, $status, $isComplete);
                $result['checkType'] = $data['type'];
            }

            $result['user'] = [
                'name' => Auth::user()->name,
            ];

            return $result;
        }

    }

    public function getVMapContents($vmapId, $userId, $vmapStatus = array(), $isComplete)
    {
        $vmapData = VMap::select('vMapId', 'formTitle', 'visionStatement', 'missionStatement')
            ->where('vMapId', $vmapId)
        // ->where('userId', $userId)
            ->with(['values' => function ($query) use ($vmapStatus, $isComplete) {
                $query
                    ->select('valueId', 'statusId', 'valueTitle', 'vMapId')
                    ->where('isDelete', true)
                    ->with(['kpis' => function ($query) use ($vmapStatus, $isComplete) {
                        $query

                            ->where('isDelete', false)
                            ->whereIn('statusId', $vmapStatus)
                            ->when($isComplete == false, function ($query) {
                                return $query->where('is_complete', false);
                            })
                            ->with(['strategy' => function ($query) use ($vmapStatus, $isComplete) {
                                $query

                                    ->where('isDelete', false)
                                    ->whereIn('statusId', $vmapStatus)
                                    ->when($isComplete == false, function ($query) {
                                        return $query->where('is_complete', false);
                                    })->with(['project' => function ($query) use ($vmapStatus, $isComplete) {
                                    $query

                                        ->where('isDelete', false)
                                        ->whereIn('statusId', $vmapStatus)
                                        ->when($isComplete == false, function ($query) {
                                            return $query->where('is_complete', false);
                                        })
                                        ->with(['criticalActivity' => function ($query) use ($vmapStatus, $isComplete) {
                                            $query
                                                ->where('is_complete', false)
                                                ->whereIn('statusId', $vmapStatus)
                                                ->when($isComplete == false, function ($query) {
                                                    return $query->where('is_complete', false);
                                                });
                                        }]);
                                }]);
                            }]);
                    }]);
            }])->with('activityTitle:ID,vmvId,valueTitle,kpiTitle,strategyTitle,projectTitle,caTitle')->first()->toArray();

        return $vmapData;
    }

}
