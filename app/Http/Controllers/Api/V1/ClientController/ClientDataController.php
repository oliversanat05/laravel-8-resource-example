<?php

namespace App\Http\Controllers\Api\V1\ClientController;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCollection;
use App\Services\ClientService\ClientDataService;

class ClientDataController extends Controller {
    use ApiResponse;
	public function __construct() {
		$this->client = new ClientDataService();
	}

	/**
	 * This function will get the client data
	 *  for the logged in user
	 * @return JSON
	 */
	public function clientData(Request $request) {

        $pageSize = $request->query('pageSize');
		$client = $this->client->getClientData($pageSize);

		return $this->successApiResponseWithoutMessage($client);
	}

    /**
     * user simulation
     *
     * @param Request $request
     * @return void
     */
    public function simulate(Request $request)
    {
        try {
            $simulateToken = $this->client->loginSimulation($request->all());
            return $this->successApiResponseWithoutMessage($simulateToken);
        } catch (\Throwable $th) {
            // throw $th;
            return $this->errorApiResponse($th->getMessage());
        }

    }
}
