<?php

namespace App\Services\wowService\TierService;

use App\Models\wowModels\Tier;

class TierService
{

    /**
     * This function create default tiers
     * for all global users`
     *
     * @param  $data Contains TierValues
     * @return \Illuminate\Http\Response
     */
    public function createDefaultTiers($data)
    {
        $getUpdatedTiers = Tier::insert($data['tiers']);
        return $getUpdatedTiers;
    }

    /**
     * This function update default tiers
     * for all global users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateDefaultTiers($newTiers, $defaultTiers)
    {
        // We already have default tiers in our database updating particular rows
        //checking whether tiers_type match betwen two array if yes then inserting value in that particular id
        foreach ($newTiers['tiers'] as $requestTier) {
            foreach ($defaultTiers as $savedTier) {
                if ($savedTier['tier_type'] == $requestTier['tier_type']) {
                    Tier::where('id', $savedTier['id'])->update($requestTier);
                    break;
                }
            }
        }
        $getUpdatedTiers = Tier::where('user_id', null)->get()->toArray();
    }

    /**
     * get default tiers from database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getDefaultTiers()
    {
        // If no user tiers  are present in database then creating returning default ones    foreach ($data['tiers'] as $requestTier) {
        $defaultTiers = Tier::where('user_id', null)->orderBy('designator','ASC')->get()->toArray();
        return $defaultTiers;
    }

    /**
     * Turn tiers on and off
     *
     * @param  int  $id as $tierId
     * @return \Illuminate\Http\Response
     */
    public function switchTierStatus($status, $id)
    {
        $updateStatus=array('status'=> $status);

        $userTiers = Tier::where('id', $id)->update($updateStatus);
        return $userTiers;

    }

    /**
     * This function create user tiers,
     * for specific user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createUserTiers($data, $userId)
    {
        $tiersData = $data['tiers'];
        // Adding user_id with the tier request
        foreach ($tiersData as $key => $value) {
            $tiersData[$key]['user_id'] = $userId;
        }
        // Inserting new tiers according to the user in Tiers table
        $userTiers = Tier::insert($tiersData);
        $getUpdatedTiers = Tier::where('user_id', $userId)->get()->toArray();

        return $getUpdatedTiers;
    }

    /**
     * This function update user tiers as a whole,
     * for specific user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUserTiers($data, $userId, $userTiers)
    {
        $tiersData = $data['tiers'];


        // We already have user tiers in our database updating particular rows
        // checking whether tiers_type match betwen two array if yes then inserting value in that particular id
        foreach ($tiersData as $key=> $requestTier) {
            foreach ($userTiers as $savedTier) {
                if ($savedTier['tier_type'] == $requestTier['tier_type']) {

                    $tiersData[$key]['id']=$savedTier['id'];
                    // Tier::where('id', $savedTier['id'])->update($requestTier);
                    break;
                }
            }
        }
        Tier::upsert($tiersData,['id'], ['min_value','max_value','status']);
        return true;
    }
}
