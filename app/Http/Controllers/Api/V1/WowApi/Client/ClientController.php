<?php

namespace App\Http\Controllers\Api\V1\WowApi\Client;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\wowRequests\ClientDataRequest;
use App\Services\wowService\ClientService\ClientService;
use App\Http\Requests\wowRequests\ClientDataUpdateRequest;

class ClientController extends Controller
{
    use ApiResponse;

    /**
     * Creating an instance of MetricAreaService
     * at begning
     */
    public function __construct()
    {
        $this->ClientService = new ClientService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $allClients = $this->ClientService->getAllClients();
            if (!$allClients) {
                return $this->unprocessableApiResponse(__('wowCore.noClients'));
            }
            return $this->successApiResponse(__('wowCore.showAllClient'), $allClients);
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Display the specified resource as per listing id.
     *
     * @param  int $id  it must be lsitingId
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $allClientsListing = $this->ClientService->getClientAsPerListing($id);
            if (!$allClientsListing) {
                return $this->unprocessableApiResponse(__('wowCore.noClients'));
            }
            return $this->successApiResponse(__('wowCore.showAllClient'), $allClientsListing);
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
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
    public function store(ClientDataRequest $request)
    {
        try {
            $createClient = $this->ClientService->createNewClient($request);
            if (!$createClient) {
                return $this->unprocessableApiResponse(__('wowCore.createClientError'));
            }
            return $this->successApiResponse(__('wowCore.createClient'), $createClient);
        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $updateClient = $this->ClientService->updateClientName($id, $request);
            if (!$updateClient) {
                return $this->unprocessableApiResponse(__('wowCore.internalServerError'));
            }
            return $this->successApiResponse(__('wowCore.clientUpdate'));
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
            $deletedClient = $this->ClientService->deleteClient($id);
            if (!$deletedClient) {
                return $this->unprocessableApiResponse(__('wowCore.internalServerError'));
            }
            return $this->successApiResponse(__('wowCore.clientDeleted'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }
}
