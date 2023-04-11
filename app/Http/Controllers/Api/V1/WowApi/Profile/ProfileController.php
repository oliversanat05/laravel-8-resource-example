<?php

namespace App\Http\Controllers\Api\V1\WowApi\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use PeterPetrus\Auth\PassportToken;

class ProfileController extends Controller
{
    use ApiResponse;
    /**
     * Get the user logged-in details
     * from the Bearer Token
     *
     * @return Object
     */
    public function userDetails(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $tokenDetails = new PassportToken($token);
            //checkig whether the token is valid or not
            if ($tokenDetails->valid) {
                // Check if token exists in DB and if doesn't return INvalid Token
                if (!$tokenDetails->existsValid()) {
                    return $this->unprocessableApiResponse(__('wowCore.invalidToken'));
                }

                $data = User::find($tokenDetails->user_id)->toArray();
                return $this->successApiResponse('Success', $data);

            } else {
                return $this->unprocessableApiResponse(__('wowCore.invalidToken'));
            }
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    public function bearerToken()
    {
        $header = $this->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
    }

}
