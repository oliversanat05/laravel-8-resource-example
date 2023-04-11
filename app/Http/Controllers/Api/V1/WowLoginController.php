<?php

namespace App\Http\Controllers\Api\V1;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Models\WowUserTrack;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class WowLoginController extends Controller
{

    use ApiResponse;
    public function login(Request $request)
    {
        try {
            $userTrack = WowUserTrack::whereSecretToken($request->secret_token)->first();

            if(!$userTrack){
                return $this->unprocessableApiResponse('Invalid User');
            }

            $user = User::where('user_id', $userTrack->user_id)->first();

            if ($user->status) {
                Auth::login($user);
                User::whereUserId($user->user_id)->update(['lastLoginDate' => Carbon::now()]);
                $tokenResult = $user->createToken(env('APP_NAME'));
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();
                $data['token'] = $tokenResult->accessToken;
                $data['token_type'] = "Bearer";
                $data['expires_at'] = Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString();
                $data['user_id'] = Auth::user()->user_id;
                $data['role_id'] = Auth::user()->role_id;
                $data['sub'] = Auth::user()->user_id;
                $data['status'] = true;
                return response()->json($data, 200);
            }

        } catch (\Throwable $th) {
            return $this->unprocessableApiResponse('Invalid User');
        }
    }

    public function keyValidation(Request $request)
    {
        try {
            $userTrack = WowUserTrack::whereSecretToken($request->secret_token)->first();

            $user = User::whereUserId($userTrack->user_id)->first();

            $data = [];

            Auth::login($user);
            $tokenResult = $user->createToken(env('APP_NAME'));
            $token = $tokenResult->token;

            if($userTrack->user_id == $request->user_id){
                User::whereUserId($user->user_id)->update(['lastLoginDate' => Carbon::now()]);
                $data = [
                    'status'=>true,
                ];

                return response()->json($data, 200);

            }else{
                $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();
                $data['token'] = $tokenResult->accessToken;
                $data['token_type'] = "Bearer";
                $data['expires_at'] = Carbon::parse(
                    $tokenResult->token->expires_at
                    )->toDateTimeString();

                $data = ['status'=>false, 'passportToken' => $data['token']];
                return response()->json($data, 422);
            }




        } catch (\Throwable $th) {

            return $this->unprocessableApiResponse('Invalid User');
        }
    }
}
