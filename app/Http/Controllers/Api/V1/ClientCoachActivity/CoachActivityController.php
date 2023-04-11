<?php

namespace App\Http\Controllers\Api\V1\ClientCoachActivity;

use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\EventCoachTrack;
use App\Http\Controllers\Controller;
use App\Http\Resources\Coach\CoachCollection;
use App\Http\Requests\clientCoachActivity\CoachActivityRequest;

class CoachActivityController extends Controller {

    use ApiResponse;


	public function trackCoachActivity(CoachActivityRequest $request) {

		$startDate = Carbon::parse($request->startDate)->format('Y-m-d');
		$endDate = Carbon::parse($request->endDate)->format('Y-m-d');
        $pageSize = $request->query('pageSize');

		try {
			$activity = EventCoachTrack::selectRaw('coachEventId, userName, sum(vmap) as vmap, sum(callmaximizer) as callmaximizer, sum(dashboard) as dashboard, sum(trackData) as trackingData,  sum(coachPath) as coachPath, coachUserName, transDate, userId, startHere')->whereBetween('transDate', [$startDate, $endDate])
				->groupBy('userId')
				->paginate($pageSize);

            return new CoachCollection($activity);
			return response()->json([
				'coachActivity' => $activity,
			], 200);
		} catch (\Exception $ex) {
            return $this->unprocessableApiResponse($ex->getMessage());
		}

		return null;

	}
}
