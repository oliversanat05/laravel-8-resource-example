<?php

namespace App\Http\Controllers\Api\V1\WowApi\Tiers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\wowModels\Tier;
use App\Services\wowService\TierService\TierService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdminTierController extends Controller
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
     * Showing default tiers created by admin
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            //first checking whether we have default values in database
            $tiersData = $this->TierService->getDefaultTiers();

            if (!$tiersData) {
                return $this->unprocessableApiResponse(__('wowCore.noDefaultTiers'));
            }
            return $this->successApiResponse(__('wowCore.defaultMetrics'), $tiersData);

        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }
    }

    /**
     * This function create default tiers
     * for all global users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            // Function to check that that tier values don't conflict each other
            if (!tiersValueValidater($data)) {
                return $this->unprocessableApiResponse(__('wowCore.tierValidationError'));
            }
            //first checking whether we have default values in database
            $defaultTiers = Tier::where('user_id', null)->get()->toArray();

            if ($defaultTiers) {
                return $this->successApiResponse(__('wowCore.defaultTiersExists'), $defaultTiers);
            } else {
                $getUpdatedTiers = $this->TierService->createDefaultTiers($data);
            }
            $getUpdatedTiers = Tier::where('user_id', null)->get()->toArray();
            return $this->successApiResponse(__('wowCore.createDefaultTiers'), $getUpdatedTiers);

        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }
    }

    /**
     * This function update default tiers
     * for all global users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $data = $request->all();
            // Function to check that that tier values don't conflict each other
            if (!tiersValueValidater($data)) {
                return $this->unprocessableApiResponse(__('wowCore.tierValidationError'));
            }
            //first checking whether we have default values in database
            $defaultTiers = Tier::where('user_id', null)->get()->toArray();

            if ($defaultTiers) {
                $getUpdatedTiers = $this->TierService->updateDefaultTiers($data, $defaultTiers);
            } else {
                return $this->successApiResponse(__('wowCore.defaultTiersUpdateError'), $defaultTiers);
            }

            $getUpdatedTiers = Tier::where('user_id', null)->get()->toArray();
            return $this->successApiResponse(__('wowCore.updateDefaultTiers'), $getUpdatedTiers);

        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }
    }

}
