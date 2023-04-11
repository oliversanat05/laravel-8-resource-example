<?php

namespace App\Http\Controllers\Api\V1\CallMaximizer;

use Auth;
use Config;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CallMaximizer\CoachCall;
use App\Models\CallMaximizer\CallMaximizerData;

class CallMaximizerOverDueComponentController extends Controller
{

    use ApiResponse;
    /**
     * method used to get the overdue activity list
     */
    public function overDueActivityList()
    {

        $callDate = Carbon::parse(Carbon::now())->format(Config::get('constants.dbDateFormat'));

        $overdueActivityList = CoachCall::select('coachCallId', 'callDate')->with('overdueList')
            ->has('overdueList')
            ->where('clientUserId', Auth::user()->user_id)
            ->get();

        return $this->successApiResponse(
            __('core.overDueActivityFetched'),
            $overdueActivityList
        );
    }

    /**
     * This function will update the overdue activity
     * @param $request
     */
    public function overDueActivityUpdate(Request $request, $id)
    {
        $dataArray = [];

        try {
            if ($id) {
                if (CallMaximizerData::updateOverDueActivity($id)) {
                    return $this->successApiResponse(
                        __('core.statusUpdate')
                    );

                } else {
                    return $this->errorApiResponse(__('core.statusNotUpdate'));
                }
            }
        } catch (\Throwable$th) {
            return $this->unprocessableApiResponse(__('core.internalServerError'));
        }

        return null;
    }
}
