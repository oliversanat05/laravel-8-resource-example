<?php

namespace App\Http\Controllers\Api\V1\WowApi\GapAnalysisScoring;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\wowRequests\ClientGapAnalysisDataRequest;
use App\Services\wowService\GapAnalysisScoringService\GapAnalysisScoringService;

class GapAnalysisScoringController extends Controller
{
    use ApiResponse;
    /**
     * Creating an instance of ClientScoreService
     * at begning
     */
    public function __construct()
    {
        $this->gapAnalysisScoringService = new GapAnalysisScoringService();
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
    public function store(ClientGapAnalysisDataRequest $request)
    {
        try {
            $updatedGapScores = $this->gapAnalysisScoringService->storeOrUpdate($request->all());
            if(! $updatedGapScores) return $this->errorApiResponse(__('wowCore.internalServerError'));
            return $this->successApiResponse(__('wowCore.gapScoreSavedSuccess'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
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
        try {
            $getGapScores = $this->gapAnalysisScoringService->getGapAnalysisData($id);
            if(! $getGapScores) return $this->errorApiResponse(__('wowCore.internalServerError'));
            return $this->successApiResponse(__('wowCore.getClientGapScore'),$getGapScores);
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
