<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Auth;
use Lang;
use Config;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CallMaximizer\CoachCall;
use App\Models\CallMaximizer\CallMaximizer;
use App\Models\CallMaximizer\CallMaximizerData;
use App\Http\Requests\CallMaximizerFilterRequest;
use App\Services\CallMaximizerService\CallMaximizerService;
use App\Http\Requests\callmaximizerRequest\StoreCallMaximizerRequest;
use App\Http\Requests\callmaximizerRequest\NextCoachingSessionRequest;
use App\Http\Requests\callmaximizerRequest\UpdateCallMaximizerRequest;
use App\Http\Requests\callmaximizerRequest\CallMaximzierComponentRequest;
use App\Http\Requests\callmaximizerRequest\CallMaximizerAssignmentComponent;

class CallMaximizerController extends Controller {

	use ApiResponse;

	public function __construct() {
		$this->maximizer = new CallMaximizerService();
	}

	/**
	 *
	 */
	public function index(CallMaximizerFilterRequest $request)
    {

        try
        {
            $post = $request->query->all();

            $coachCall = CallMaximizer::processCoachCall();

            $coachCallData = CoachCall::where('coachCallId', $post['coachCallId'])->with(['callMaximizerData'])->get();

            return response()->json([
                'callMaximizer' => $coachCallData,
                "coachCall" => $coachCall
            ]);

        } catch (\Exception $th) {
            return $this->errorApiResponse($th->getMessage());
        }
	}

	/**
	 * This function will update the
	 * next coaching data
	 * @param NextCoachingSessionRequest $request
	 * @return JSON
	 */
	public function updateNextCoachingSession(NextCoachingSessionRequest $request, $id) {

		try {
			$response = $this->maximizer->nextCoachingSession($request->all(), $id);

			if ($response['response']) {
				return $this->successApiResponse(
					__('core.statusUpdate'), $response['dataArray']['nextCallDate']
				);
			} else {
				return $this->errorApiResponse(__('core.nextScheduleError'));
			}
		} catch (\Throwable$th) {
			return $this->errorApiResponse(__('core.internalServerError'));
		}
	}



	/**
	 * This function will create the callmaximizer
	 * @param StoreCallMaximizerRequest $request
	 * @return JSON
	 */
	public function store(StoreCallMaximizerRequest $request) {

		try {
			$response = CoachCall::getCallMaximizerDate($request->newCallMax);

			if ($response->isEmpty()) {
				$response = $this->maximizer->createNewCallMaximizer($request->all());

                return $this->successApiResponse(__('core.callmaximizerAdded'), $response);

			} else {
				return $this->errorApiResponse(__('core.alreadyExists'));
			}
		} catch (\Throwable$th) {
			return $this->unprocessableApiResponse(__('core.internalServerError'));
		}
		return null;
	}

	/**
	 * This function will delete the callmaximizer date
	 * @param $request
	 */
	public function destroy(Request $request, $id) {
		try {
			if (CoachCall::checkCallExists($id)) {
				$data = $this->maximizer->deleteCoachCallData($id);

				return $this->successApiResponse(
					__('core.callMaximizerDelete')
				);
			} else {
				return $this->errorApiResponse(__('core.exists'));
			}
		} catch (\Throwable$th) {
			return $this->unprocessableApiResponse(__('core.internalServerError'));
		}
	}

	/**
	 * This function will update the callmaximizer
	 * @param UpdateCallMaximizerRequest $request
	 * @return JSON
	 */
	public function update(UpdateCallMaximizerRequest $request, $id) {

		try {
			if (CoachCall::checkCallExists($id)) {
				$data =$this->maximizer->updateCallMaximizer($id, $request->date);

				return $this->successApiResponse(
					__('core.callMaximizerUpdate'), $data
				);
			} else {
				return $this->errorApiResponse(__('core.exists'));
			}
		} catch (\Throwable$th) {
			return $this->unprocessableApiResponse(__('core.internalServerError'));
		}
	}


    /**
     * get all the callmaximizer for the auth user
     *
     * @return void
     */
    public function getCallMaximizerData()
    {
        try {
            $callMaximizer = CoachCall::select('coachCallId', 'callDate', 'nextCallDate', 'timeZoneId', 'nextTimeZoneId', 'callTimeId', 'nextCallTimeId')
                    ->with(['timezone', 'nextTimezone', 'callTime', 'nextCallTime'])
                    ->where('clientUserId', Auth::user()->user_id)
                    ->orderBy('callDate', 'desc')
                    ->get();

            return $this->successApiResponse(__('core.callMaximizer'), $callMaximizer);
        } catch (\Exception $th) {
            return $this->errorApiResponse($th->getMessage());
        }
    }

    /**
     * delete the callmaximizer assignment component
     *
     * @param Request $request
     * @return void
     */
    public function deleteCallMaximizerAssignment(CallMaximizerAssignmentComponent $request)
    {
        $response = $this->maximizer->deleteCallMaximizerAssignmentComponent($request->id);

        if($response){
            return $this->successApiResponse(__('core.callMaximizerDelete'));
        } else {
            return $this->unprocessableApiResponse(__('core.callMaximizerComponentDelete'));
        }
    }
}
