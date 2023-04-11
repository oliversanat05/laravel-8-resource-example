<?php

namespace App\Http\Controllers\Api\V1\WowApi\ClientScore;

use App\Http\Controllers\Controller;
use App\Http\Requests\wowRequests\ClientScoringDataRequest;
use App\Models\wowModels\Client;
use App\Models\wowModels\ClientScore;
use App\Services\wowService\ClientMetricDataService\ClientMetricDataService;
use App\Services\wowService\ClientScoreService\ClientScoreService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\wowRequests\ClientScoringUpdateDataRequest;

class ClientScoreController extends Controller
{

    use ApiResponse;
    /**
     * Creating an instance of ClientScoreService
     * at begning
     */
    public function __construct()
    {
        $this->ClientScoreService = new ClientScoreService();
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
    public function store(ClientScoringDataRequest $request)
    {
        $listingId = $request->listing_id;
        $clientScoreData = [];
        try {
            DB::beginTransaction();
            foreach ($request->client_data as $index => $client) {
                foreach ($client['scores'] as $key => $value) {
                    $value['client_id'] = $client['client_id'];
                    $value['listing_id'] = $listingId;
                    array_push($clientScoreData, $value);
                }
            }
            $insertScores = $this->ClientScoreService->massInsertClientScores($clientScoreData);
            if ($insertScores) {
                DB::commit();
                return $this->successApiResponse(__('wowCore.clientScoreAdded'));
            }
            return $this->unprocessableApiResponse(__('wowCore.clientScoreAddedFailed'));
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Service to get validate listing id
            $isIdExists = $this->clientMetricDataService->validateListingId($id);
            if ($isIdExists) {
                // Service to get record from database
                $allClients = $this->ClientScoreService->getAllClientAsPerListingId($id);
                return $this->successApiResponseWithNullData(__('wowCore.fetchClientScoreData'), $allClients);
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
    public function update(ClientScoringUpdateDataRequest $request, $id)
    {
        try {
            $data = $request->all();
            $updatedRecords = $this->ClientScoreService->updateClientDataWithScores($data,$id);
            if (!$updatedRecords) {
                return $this->unprocessableApiResponse(__('wowCore.errorUpdate'));
            }
            return $this->successApiResponse(__('wowCore.clientDataUpdated'));
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
        try {
            $removedClient = $this->ClientScoreService->removeClientFromDb($id);
            if ($removedClient) {
                return $this->successApiResponse(__('wowCore.clientRemove'));
            } else if ($removedClient == null) {
                return $this->unprocessableApiResponse(__('wowCore.noPrivelegeToDeleteClient'));
            }
            return $this->unprocessableApiResponse(__('wowCore.clientNotExists'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }
}
