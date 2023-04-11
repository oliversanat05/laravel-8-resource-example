<?php

namespace App\Http\Controllers\Api\V1\wowAccess;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ModuleAccess\ModuleAccessService;

class WowAccessController extends Controller
{
    use ApiResponse;

    private $module;

    public function __construct()
    {
        $this->module = new ModuleAccessService();
    }

    /**
     * for getting the user who have the wow access
     *
     * @return void
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('pageSize');
        $access = $this->module->getWowAccess($pageSize);
        return $this->successApiResponseWithoutMessage($access);
    }

    /**
     * update the access for the wow
     *
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {

        try {
            $response =  $this->module->grantWowAccess($request->access);

            if($response) {
                return $this->successApiResponse(__('core.wowAccessSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.wowAccessError'));
            }
        } catch (\Throwable $th) {
            return $this->errorApiResponse($th->getMessage());
        }
    }
}
