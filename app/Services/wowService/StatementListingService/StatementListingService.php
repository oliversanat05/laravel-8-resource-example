<?php

namespace App\Services\wowService\StatementListingService;

use App\Models\wowModels\MetricArea;
use Illuminate\Support\Facades\Auth;
use App\Models\wowModels\ClientMetricData;
use App\Models\wowModels\StatementListing;
use App\Models\wowModels\WowTracker;
use Illuminate\Validation\ValidationException;

class StatementListingService
{
    /**
     * Fetch all the StatementListing from DB for particular user
     *
     * @return \object
     */
    public function getAllUserStatementListing()
    {
        $userId = Auth::user()->user_id;
        $statementListing = StatementListing::where('user_id', $userId)->orderBy('statement_date', 'DESC')->get();
        return $statementListing;
    }

    /**
     * Create a new statement listing  in StatementListing
     * @param $data
     * @return \object
     */
    public function createNewStatementListing($data)
    {
        try {
            //Creating a new statementListing in DB
            $data['user_id'] = Auth::user()->user_id;
            $statementListing = StatementListing::create($data);
            return $statementListing;
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['failed' => 'Something went wrong']);
        }
    }

    /**
     * Currently this function is not used anywhere
     * @param StatementListing $StatementListingId
     * @return \object
     */
    public function getSpecificStatementListing($StatementListingId)
    {
        $statementListing = StatementListing::with('clientMetricData')->where('id', $StatementListingId)->get();
        return $statementListing;
    }

    /**
     * Get Last Listing id of the user
     * @return \object
     */
    public function getLastListingId()
    {
        $statementListing = StatementListing::where('user_id', Auth::user()->user_id)
            ->orderBy('id', 'desc')->limit(1)->get(['id', 'statement_date'])->toArray();
        return $statementListing;
    }

    /**
     * Specific statement list from Database
     * Group it and add metric type with it in response
     * @param StatementListing $StatementListingId
     * @return \object
     */
    public function getSpecificStatementListingMetricAreaType($StatementListingId)
    {
        // Getting lsiting Data with metric_ids in a group
        $statementListing = StatementListing::with(['ClientMetricData' => function ($query) {
            $query->groupBy('metric_id');
        }])->where('id', $StatementListingId)->get()->toArray();

        $ids = [];
        // Storing all metric_ids in ids array
        foreach ($statementListing[0]['client_metric_data'] as $index => $value) {
            $ids[$index] = $value['metric_id'];
        }
        // Fetching metric_type of ids
        $metricTypeData = MetricArea::whereIn('id', $ids)->get('metric_type')->toArray();


        // inserting type in statementListing and returning the variable
        foreach ($statementListing[0]['client_metric_data'] as $key => $value) {
            $statementListing[0]['client_metric_data'][$key]['metric_type'] = $metricTypeData[$key]['metric_type'];
        }
        return $statementListing;
    }

    /**
     * Deleting a listing record from database
     * @param StatementListing $StatementListingId
     * @return \object
     */
    public function deleteSpecificStatementListing($StatementListingId)
    {
        ClientMetricData::where('listing_id',$StatementListingId)->delete();
        WowTracker::where('listing_id',$StatementListingId)->delete();

        $statementListing = StatementListing::where([
            ['id', $StatementListingId],
            ['user_id', Auth::user()->user_id]
        ])->delete();
        return $statementListing;
    }

    /**
     * Get user id from the statement Listing Id
     */
    public function getUseridByClient($listingId)
    {
        $userId = StatementListing::where('id', $listingId)->get('user_id')
            ->pluck('user_id')->toArray();
        return $userId[0];
    }

    /**
     * Clone Listing Data
     * @param StatementListing $StatementListingId
     * @return \object
     */
    public function replicateData($RecievedRequest)
    {
        $model = StatementListing::find($RecievedRequest['listing_id']);
        $model->statement_date = $RecievedRequest['new_statement_date'];
        $newModel = $model->duplicate();
        $newModel->push();
        $data = ['new_listing_id' => $newModel->id];
        return $data;
    }
}
