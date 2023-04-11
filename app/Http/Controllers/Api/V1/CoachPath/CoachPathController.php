<?php

namespace App\Http\Controllers\Api\V1\CoachPath;

use App\Http\Controllers\Controller;
use App\Http\Requests\coachPathRequest\CoachingReadinessDateAddRequest;
use App\Http\Requests\coachPathRequest\EditCoachingReadinessDateRequest;
use App\Services\CoachPath\CoachPathService;
use App\Traits\ApiResponse;
use Config;
use DB;
use Illuminate\Http\Request;

class CoachPathController extends Controller
{

    use ApiResponse;

    private $coachPath;

    public function __construct()
    {
        $this->coachPath = new CoachPathService();
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CoachingReadinessDateAddRequest $request)
    {
        try {

            $coachingReadinessDate = $request['date'];

            $checkDateExists = $this->coachPath->checkFormDateExists($coachingReadinessDate, Config::get('statistics.startHere.coachingReadiness'));

            if ($checkDateExists) {
                DB::beginTransaction();

                $response = $this->coachPath->storeCoachingReadinessDate($coachingReadinessDate, 'coach');

                DB::commit();

                if ($response) {
                    return $this->successApiResponse(__('core.coachPathSuccess'));
                } else {
                    return $this->unprocessableApiResponse(__('core.coachPathError'));
                }
            } else {
                return $this->unprocessableApiResponse(__('core.coachPathDateExistsError'));
            }

        } catch (\Throwable$th) {
            DB::rollback();
            throw $th;
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EditCoachingReadinessDateRequest $request, $date)
    {
        try {
            DB::beginTransaction();

            $response = $this->coachPath->editCoachingReadinessDate($request->date, $date, 'coach');

            DB::commit();
            if ($response) {
                return $this->successApiResponse(__('core.coachingReadinessEditSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.coachingReadinessEditError'));
            }
        } catch (\Throwable$th) {
            DB::rollback();

            return $th->getMessage();
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($date)
    {
        try {
            DB::beginTransaction();

            $response = $this->coachPath->deleteCoachingReadiness($date, 'coach');

            DB::commit();

            if ($response) {
                return $this->successApiResponse(__('core.coachingReadinessDeleteSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.coachingReadinessDeleteError'));
            }
        } catch (\Throwable$th) {

            DB::rollback();
            throw $th;
        }
    }

    /**
     * get the coach path content
     *
     * @param Request $request
     * @return void
     */
    public function getCoachPathData(Request $request)
    {
        $data = $this->coachPath->getInitialFormData(Config::get('statistics.startHere.coachingReadiness'));
        return $data;
    }

    /**
     * for saving the coaching readiness questions data
     *
     * @param Request $request
     * @return void
     */
    public function saveCoachingReadinessData(Request $request, $surveyDate)
    {
        try {

            DB::beginTransaction();
            $data = $request->all();
            $response = $this->coachPath->saveCoachingReadinessQuestionsData($data, $surveyDate);
            DB::commit();
            if ($response) {
                return $this->successApiResponse(__('core.coachingReadinessDataSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.coachingReadinessDataError'));
            }
        } catch (\Throwable$th) {

            DB::rollback();
            throw $th;
        }
    }
}
