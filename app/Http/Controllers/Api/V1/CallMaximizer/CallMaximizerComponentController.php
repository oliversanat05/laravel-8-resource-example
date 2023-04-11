<?php

namespace App\Http\Controllers\Api\V1\CallMaximizer;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CallMaximizer\CallMaximizerData;
use App\Services\CallMaximizerService\CallMaximizerService;
use App\Http\Requests\callmaximizerRequest\CallMaximzierComponentRequest;

class CallMaximizerComponentController extends Controller
{

    use ApiResponse;

    public function __construct()
    {
        $this->maximizer = new CallMaximizerService();
    }
    /**
     * This function will update the callmaximizer
     * @param CallMaximzierComponentRequest $request
     * @return JSON
     */
    public function update(CallMaximzierComponentRequest $request, $id)
    {
        try {
            if (CallMaximizerData::where('coachCallId', $id)->exists()) {
                $callMaximizerUpdate = $this->maximizer->updateData($request->params, $id);

                if ($callMaximizerUpdate) {
                    return $this->successApiResponse(
                        __('core.callMaximizerUpdate')
                    );
                } else {
                    return $this->unprocessableApiResponse(__('core.callMaximizerNotUpdate'));
                }
            } else {
                return $this->unprocessableApiResponse(__('core.exists'));
            }
        } catch (\Throwable$th) {

            throw $th;
            return $this->errorApiResponse(__('core.internalServerError'));
        }

    }
}
