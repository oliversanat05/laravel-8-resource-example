<?php

namespace App\Http\Controllers\Api\V1\WowApi\Tiers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\wowModels\Tier;
use App\Services\wowService\TierService\TierService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Requests\wowRequests\TiersRequest;

class TierController extends Controller
{
    use ApiResponse;

    /**
     * Creating an instance of TierServices
     * at begning
     */
    public function __construct()
    {
        $this->TierService = new TierService();
    }

    /**
     * Display the user tiers, If user tiers are not present
     * then return the default tiers.
     *
     * @param  int  $id is user_id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $userId = $id;
            $tiersData = null; # Storing tiers data and returning this variable\
            //first checking whether we have user tiers values in database
            $userTiers = Tier::where('user_id', $userId)->orderBy('designator','ASC')->get()->toArray();
            // if We already have user tiers in our database then return user Tiers
            if ($userTiers) {
                return $this->successApiResponse(__('wowCore.userTiers'), $userTiers);
            }
            $tiersData = $this->TierService->getDefaultTiers();
            return $this->successApiResponse(__('wowCore.defaultTiersForUser'), $tiersData);
        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }
    }

   /**
     * Showing Enabled Tiers not present show
     * then the default tiers.
     *
     * @param  int  $id is user_id
     * @return \Illuminate\Http\Response
     */
    public function showEnabled($id)
    {
        try {
            $userId = $id;
            $tiersData = null; # Storing tiers data and returning this variable\
            //first checking whether we have user tiers values in database
            $userTiers = Tier::where('user_id', $userId)->where('status',1)->orderBy('designator','ASC')->get()->toArray();
            // if We already have user tiers in our database then return user Tiers
            if ($userTiers) {
                return $this->successApiResponse(__('wowCore.userTiers'), $userTiers);
            }
            $tiersData = $this->TierService->getDefaultTiers();
            return $this->successApiResponse(__('wowCore.defaultTiersForUser'), $tiersData);
        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }
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

        try {
            $data = $request->all();
            // Function to check that that tier values don't conflict each other
            if (!tiersValueValidater($data)) {
                return $this->unprocessableApiResponse(__('wowCore.tierValidationError'));
            }
            //first checking whether we have user tiers values in database
            $userTiers = Tier::where('user_id', $id)->get(['id', 'tier_type'])->toArray();
            if ($userTiers) {
                $getUpdatedTiers = $this->TierService->updateUserTiers($data, $id, $userTiers);
            } else {
                $getUpdatedTiers = $this->TierService->createUserTiers($data, $id);
            }
            return $this->successApiResponse(__('wowCore.updateUserTiers'), $getUpdatedTiers);
        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }
    }

    /**
     * Turn tiers on and off
     *
     * @param  int  $id as $tierId
     * @return \Illuminate\Http\Response
     */
    public function changeTierStatus(Request $request, $id)
    {
        try {
            $tierId = $id;
            $status = $request->status;
            $responseMessage = $status ? __('wowCore.tierTurnedOn') : __('wowCore.tierTurnedOff');
            // Get tiers
            $userTiers = $this->TierService->switchTierStatus($status, $id);
            return $this->successApiResponse($responseMessage, $userTiers);
        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }
    }

}
