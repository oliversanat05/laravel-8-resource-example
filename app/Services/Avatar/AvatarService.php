<?php

namespace App\Services\Avatar;

use App\Models\AvatarDetails;
use Auth;

class AvatarService
{
    /**
     * for saving the avatar data
     *
     * @param [type] $data
     * @return void
     */
    public function saveOrUpdateAvatarData($data)
    {
        $avatarDetails = AvatarDetails::updateOrCreate([
            'userId' => Auth::user()->user_id,
            'healthId' => $data['healthId'],
        ], [
            'userId' => Auth::user()->user_id,
            'healthId' => $data['healthId'],
            'topType' => $data['topType'],
            'accessoriesType' => $data['accessoriesType'],
            'hairColor' => $data['hairColor'],
            'facialHairType' => $data['facialHairType'],
            'clotheType' => $data['clotheType'],
            'eyeType' => $data['eyeType'],
            'eyebrowType' => $data['eyebrowType'],
            'mouthType' => $data['mouthType'],
            'skinColor' => $data['skinColor'],
            'clotheColor' => $data['clotheColor']
        ]);

        return $avatarDetails;
    }

    /**
     * fetch the avatar details for the logged in user
     *
     * @return void
     */
    public function getAvatarDetails()
    {
        $avatarDetails = AvatarDetails::where('userId', Auth::user()->user_id)->get();

        return $avatarDetails;
    }
}
