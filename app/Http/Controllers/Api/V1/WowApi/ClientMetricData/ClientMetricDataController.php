<?php

namespace App\Http\Controllers\Api\V1\WowApi\ClientMetricData;

use App\Http\Controllers\Controller;
use App\Http\Requests\wowRequests\ClientMetricDataRequest;
use App\Http\Requests\wowRequests\ClientMetricDataUpdateRequest;
use App\Http\Requests\wowRequests\ClientMetricDataFilterRequest;
use App\Services\wowService\ClientMetricDataService\ClientMetricDataService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
class ClientMetricDataController extends Controller
{
    use ApiResponse;

    /**
     * Creating an instance of MetricAreaService
     * at begning
     */
    public function __construct()
    {
        $this->clientMetricDataService = new ClientMetricDataService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ClientMetricDataRequest $request)
    {
        try {
            $receivedArray = $request->metric_id;
            $oldValueInDb = $this->clientMetricDataService->getAllListingGroupByMetric($request->listing_id,$request->metric_type);

            if (count($oldValueInDb)) {
                $valueNotPresentInDb = array_diff($receivedArray, $oldValueInDb);
                $valueAlreadyPresentInDb = array_diff($oldValueInDb, $receivedArray);
                if (count($valueAlreadyPresentInDb)) {
                    $deleteOldData = $this->clientMetricDataService->deleteMetricFromDb($request->listing_id, $valueAlreadyPresentInDb);
                    if (!$deleteOldData) {
                        return $this->unprocessableApiResponse(__('wowCore.metricCreationError'));
                    }
                }
                $request['metric_id'] = $valueNotPresentInDb;
            }
            $clientMetricData = $request->all();
            $clientMetricHeadings = $this->clientMetricDataService->getMetricHeadingIdByType($clientMetricData['metric_type']);
            // Variable to Store metric Details before inserting it in database
            $metricTableDataCreator = [];
            // This loop will intialized the data in Database format before inserting
            foreach ($clientMetricData['metric_id'] as $metricId) {
                foreach ($clientMetricHeadings as $metricHeading) {
                    $tableRow = [
                        "metric_id" => $metricId,
                        "listing_id" => $clientMetricData['listing_id'],
                        "metric_heading_id" => $metricHeading['id'],
                        "created_at" => Carbon::today()->toDateTimeString()
                    ];
                    array_push($metricTableDataCreator, $tableRow);
                }
            }
            // Service to insert record in database
            $clientMetricCreatedData = $this->clientMetricDataService->createClientMetricData($metricTableDataCreator, $request->listing_id);
            if ($clientMetricCreatedData) {
                return $this->successApiResponse(__('wowCore.clientMetricDataSaved'), $clientMetricCreatedData);
            }
            return $this->unprocessableApiResponse(__('wowCore.metricCreationError'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id  it must be lsitingId
     * @return \Illuminate\Http\Response
     */
    public function show(ClientMetricDataFilterRequest $request, $id)
    {
        try {
            // Service to get record from database
            $isIdExists = $this->clientMetricDataService->validateListingId($id);
            if ($isIdExists) {
                $fetchAllListingData = $this->clientMetricDataService->getAllListingDataWithRelationships($id,$request->type);
                return $this->successApiResponseWithNullData(__('wowCore.fetchMetricData'), $fetchAllListingData);
            }
            return $this->unprocessableApiResponse(__('wowCore.statusClientMetricDataError'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ClientMetricDataUpdateRequest $request, $id)
    {
        try {
            // Service to get updated record from database
            $isIdExists = $this->clientMetricDataService->validateListingId($id);
            if ($isIdExists) {
                // Service to update record in database
                $isDataUpdated = $this->clientMetricDataService->updateClientMetricData($request);
                if ($isDataUpdated) {
                    return $this->successApiResponse(__('wowCore.clientMetricDataSuccessUpdate'));
                }
            }
            return $this->unprocessableApiResponse(__('wowCore.statusClientMetricDataError'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
