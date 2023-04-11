<?php

namespace App\Services\wowService\CLientMetricDataService;

use App\Models\wowModels\ClientMetricData;
use App\Models\wowModels\MetricHeading;
use App\Models\wowModels\StatementListing;
use App\Services\wowService\StatementListingService\StatementListingService;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
class ClientMetricDataService
{
    /**
     * Get metric Type and return metric heading ids
     *
     * @return \object
     */
    public function getMetricHeadingIdByType($metricType)
    {
        $metricHeadingIds = MetricHeading::where('metric_type', $metricType)->get('id')->toArray();
        return $metricHeadingIds;
    }

    /**
     * Get metric Type and return metric heading ids
     *
     * @return \object
     */
    public function createClientMetricData($multipleClientMetricData, $listingId)
    {
        $listingService = new StatementListingService();
        if (count($multipleClientMetricData)) {
            ClientMetricData::insert($multipleClientMetricData);
        }
        $createdListingData = $listingService->getSpecificStatementListingMetricAreaType($listingId);
        return $createdListingData;
    }

    /**
     * Get all listing Data according to Listing id
     *
     * @return \object
     */
    public function getAllListingData($listingId)
    {
        $clientMetricListingData = ClientMetricData::where('listing_id', $listingId)->get()->toArray();
        return $clientMetricListingData;
    }

    /**
     * Get all listing Data group by metric Id
     *
     * @return \object
     */
    public function getAllListingGroupByMetric($listingId, $metricType)
    {
        $metricIds = [];
        $clientMetricListingData = ClientMetricData::with('metricArea:id,metric_type')
                                    ->where('listing_id', $listingId)->groupBy('metric_id')->get('metric_id')->toArray();

        foreach ($clientMetricListingData as $key => $clientMetricData) {
            foreach ($clientMetricData['metric_area'] as $index => $metricArea) {
                if ($metricArea['metric_type'] == $metricType) {
                    array_push($metricIds, $clientMetricListingData[$key]['metric_id']);
                }
            }
        }
        return $metricIds;
    }

    /**
     * Get all listing Data group by metric Id
     *
     * @return \object
     */
    public function deleteMetricFromDb($listingId, $metricValueArray)
    {
        $deletedOldData = ClientMetricData::where('listing_id', '=', $listingId)
            ->where(function ($query) use ($metricValueArray) {
                $query->whereIn('metric_id', $metricValueArray);
            })->delete();

        if (!$deletedOldData) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get all listing Data with Relationhip
     *
     * @return \object
     */
    public function getAllListingDataWithRelationships($listingId,$type)
    {
        $clientMetricListingDataRelationship = ClientMetricData::with(['metricArea'])->where('listing_id',$listingId)
        ->whereRelation('metricArea', 'metric_type', '=', $type)
                    ->get()
                    ->toArray();

        $formatData = formatClientScoreData($clientMetricListingDataRelationship);
        return $formatData;
    }

    /**
     * Validating Listing Id
     *
     * @return \object
     */
    public function validateListingId($listingId)
    {
        $userId = Auth::user()->user_id;
        $dbUserIds = StatementListing::where('id', $listingId)->get('user_id')->toArray();
        if (count($dbUserIds) == 0 || $dbUserIds[0]['user_id'] != $userId) {
            return false;
        }
        return true;
    }

    /**
     * Get metric Type and return metric heading ids
     *
     * @return \object
     */
    public function updateClientMetricData($request)
    {
        try {
            DB::beginTransaction();
            # Variable to store all ids from request.
            $formatedData = [];
            # formatting data according to upsert
            foreach ($request->all() as $key => $value) {
                $data=['id'=>$value['metric_data_id'],'metric_value'=>$value['metric_data_value']];
                array_push($formatedData, $data);
            }
            ClientMetricData::upsert($formatedData,['id'],['metric_value']);
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
