<?php

namespace App\Http\Controllers\Api\V1\Delegates;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\DelegateUserService\DelegateUserService;
use App\Http\Requests\delegateRequest\DelegateChangeStatusRequest;

class DelegateStatusController extends Controller
{

    use ApiResponse;

    public function __construct(){
        $this->delegate = new DelegateUserService();
    }
    /**
     * this function will update the status of the delegate form active to inactive or vice-versa
     * @param DelegateChangeStatusRequest $request
     * @return JSON
     */
    public function changeDelegateStatus(DelegateChangeStatusRequest $request, $id)
    {
        try {
            $data = $request->status;
            $status = $this->delegate->updateDelegateStatus($data, $id);

            if ($status) {
                return $this->successApiResponse(__('core.delgateStatusSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.delegateStatusError'));
            }
        } catch (\Throwable$th) {
            return $this->unprocessableApiResponse(__('core.internalServerError'));
        }

    }
}
