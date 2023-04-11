<?php

namespace App\Http\Controllers\Api\V1\CoachPath;

use App\Http\Controllers\Controller;
use App\Services\CoachPath\CoachPathService;
use App\Traits\ApiResponse;
use Config;
use DB;
use Illuminate\Http\Request;

class SweetSpotController extends Controller
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
        $data = $this->coachPath->getInitialFormData(Config::get('statistics.startHere.sweetSpotAnalysis'));
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $sweetSpotAnalysisDates = $request['date'];

            $checkDateExists = $this->coachPath->checkFormDateExists($sweetSpotAnalysisDates, 12);

            if ($checkDateExists) {
                DB::beginTransaction();

                $response = $this->coachPath->storeCoachingReadinessDate($sweetSpotAnalysisDates, 'sweet-spot-analysis');

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
            return $this->errorApiResponse($th->getMessage());
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
    public function update(Request $request, $date)
    {
        try {
            DB::beginTransaction();

            $response = $this->coachPath->editCoachingReadinessDate($request->date, $date, 'sweet-spot-analysis');

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

            $response = $this->coachPath->deleteCoachingReadiness($date, 'sweet-spot-analysis');

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
     * for saving the sweet spot analysis questions data
     *
     * @param Request $request
     * @return void
     */
    public function saveSweetSpotAnalysisData(Request $request, $surveyDate)
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
