<?php
namespace App\Services\ClientService;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use Laravel\Passport\Token;

class ClientDataService
{

    /**
     * get the logged in users client
     *
     * @param Integer $pageSize
     * @return collection
     */
    public function getClientData($pageSize)
    {
        return User::select('name', 'created_at', 'user_id', 'user_image', 'user_name', 'lastLoginDate', 'status')->whereHas('coach', function ($query) {
            $query->where('coachUserId', Auth::user()->user_id)
                ->orWhereRaw("find_in_set (" . Auth::user()->user_id . ", additionalCoach)");
        })->paginate($pageSize);
    }

    /**
     * login simulation
     *
     * @param $data
     * @return void
     */
    public function loginSimulation($data)
    {
        $user = User::where('user_id', $data['user_id'])->where('status', true)->first();

        $token = $user->createToken('simulate');
        $simulateToken = $token->token;
        $simulateToken->expires_at = Carbon::now()->addWeeks(1);
        $simulateToken->save();

        //update the last login date for the simulated user
        User::whereUserId($data['user_id'])->update([
            'lastLoginDate' => Carbon::now()
        ]);

        $authHeader = explode('.', $token->accessToken);
        $authToken = $authHeader[1];

        $tokenParts = explode('.', $authToken);

        $tokenHeader = $tokenParts[0];
        $tokenHeaderJson = base64_decode($tokenHeader);

        $tokenHeaderArray = json_decode($tokenHeaderJson, true);
        $tokenId = $tokenHeaderArray['jti'];

        $user = Token::find($tokenId)->user;

        $tokenArray = [
            'token' => $token->accessToken,
            'name' => $user->name,
            'user_id' => $user->user_id
        ];

        return $tokenArray;

    }
}
