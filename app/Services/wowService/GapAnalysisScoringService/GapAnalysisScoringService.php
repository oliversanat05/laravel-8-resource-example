<?php

namespace App\Services\wowService\GapAnalysisScoringService;

use App\Models\wowModels\Client;
use Illuminate\Support\Facades\DB;
use App\Models\wowModels\GapAnalysisScore;


class GapAnalysisScoringService
{

    /**
     * Storing or updating Gap Analysis in client Scoring
     *
     * @return \object
     */
    public function storeOrUpdate($scoreData)
    {

        try {
            DB::beginTransaction();
            $RequestData = $scoreData;
            if (isset($RequestData['scores'])) {
                foreach ($RequestData['scores'] as $index => $score) {
                    $RequestData['scores'][$index]['listing_id'] = $RequestData['listing_id'];
                }
                GapAnalysisScore::upsert($RequestData['scores'], ['gap_analysis_unique'], ['score']);
            }
            if (isset($RequestData['clients'])) {
                Client::upsert($RequestData['clients'], ['id'], ['conversation_date', 'note']);
            }
            DB::commit(); return true;

        } catch (\Exception $e) {
            DB::rollback();  return null;
        }
    }

    /**
     * Get Gap Analysis Scoring Data
     *
     * @return \object
     */
    public function getGapAnalysisData($id)
    {
        $data = Client::with('gapAnalysis')->where('listing_id', $id)->get();
        return $data;
    }
}
