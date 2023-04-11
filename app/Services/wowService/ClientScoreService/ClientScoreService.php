<?php

namespace App\Services\wowService\ClientScoreService;

use App\Models\wowModels\Client;
use App\Models\wowModels\ClientScore;
use App\Models\wowModels\MetricArea;
use App\Services\wowService\StatementListingService\StatementListingService;
use DB;
use Illuminate\Support\Facades\Auth;

class ClientScoreService
{

    /**
     * Get all clients Data according to Listing id
     *
     * @return \object
     */
    public function getAllClientAsPerListingId($listingId)
    {
        $metricAreaIds = [];

        $allclients = Client::with(['clientScore'])->where('listing_id',$listingId)
        ->whereRelation('clientScore', 'listing_id', '=', $listingId)->get()->toArray();

        if(count($allclients)){
        foreach ($allclients as $key => $value) {
            foreach ($value['client_score'] as $key1 => $value1) {
                array_push($metricAreaIds,$value1['metric_area_id']);
            }
        }

        $idsOrdered = implode(',', $metricAreaIds);
        $data = MetricArea::whereIn('id', $metricAreaIds)->orderByRaw("FIELD(id, $idsOrdered)")->get()->toArray();

        foreach ($allclients as $key => $value) {
            foreach ($value['client_score'] as $key1 => $value1) {
                foreach ($data as $key2 => $value2) {
                    if($value2['id']==$value1['metric_area_id']){
                        $allclients[$key]['client_score'][$key1]['metric_type'] = $data[$key2]['metric_type'];
                        $allclients[$key]['client_score'][$key1]['title'] = $data[$key2]['title'];
                        break;
                    }
                }
            }
        }
        }
        return $allclients;
    }

    /**
     * Function to create a client in a database
     *
     * @return \string clientId
     */
    public function createNewClient($clientName, $listingId, $totalScore)
    {
        $createdClient = Client::create([
            'name' => $clientName,
            'listing_id' => $listingId,
            'total_score' => $totalScore,
        ]);
        return $createdClient->id;
    }

    /**
     * Function to insert client score as a mass Insert
     *
     * @return \string clientId
     */
    public function massInsertClientScores($clientScoreData)
    {
        $insertedScores = ClientScore::insert($clientScoreData);
        return $insertedScores;
    }

    /**
     * Get Listing Id from ClientId
     *
     * @return \string clientId
     */
    public function listIdFromClientId($clientId)
    {
        $listingId = Client::where('id', $clientId)->get('listing_id')->pluck('listing_id')->toArray();
        return $listingId[0];
    }

    /**
     * Remove a client From database
     *
     * @return \string clientId
     */
    public function removeClientFromDb($clientId)
    {
        $listingId = $this->listIdFromClientId($clientId);
        $listingService = new StatementListingService();
        $userId = $listingService->getUseridByClient($listingId);

        if ($userId != Auth::user()->user_id) {
            return null;
        }
        $clientDeleted = Client::destroy($clientId);
        return $clientDeleted;
    }

    /**
     * Get Client details with Scores
     *
     * @return \string clientId
     */
    public function getclientDataWithScores($clientIds)
    {
        $data = Client::whereIn('id', $clientIds)->with('clientScore:client_id,id,score')->get(['id', 'total_score'])->toArray();
        return $data;
    }

    /**
     * Get Client details with Scores
     *
     * @return \string clientId
     */
    public function updateClientDataWithScores($data,int $listingId)
    {
        try {
            DB::beginTransaction();
            # Seprating all the ids from the the data and storing it in ids
            foreach($data['client_data'] as $key => $value) {
                $data['client_data'][$key]['listing_id']=$listingId;
            }
            // Mass updating clients Score
            ClientScore::upsert($data['client_data'],['client_scores_unique'], ['score']);
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
