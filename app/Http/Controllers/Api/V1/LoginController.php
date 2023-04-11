<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\authRequest\LoginRequest;
use App\Traits\ApiResponse;
use Lang;

class LoginController extends Controller
{

    use ApiResponse;
    /**
     * method used to login the user.
     *
     * @param LoginRequest $request
     *
     * @return json
     *
     */
    public function login(LoginRequest $request){

        try {
            $credentials = [
                'emailOrUsername' => $request['emailOrUsername'],
                'password' => $request['password'],
            ];

            //remember me
            $remember = $request['rememberMe'] ? 1 : 0;

            $attempt = ['password' => $credentials['password'], 'status' => 1, 'deleted_at' => null];

            /**
             *
             * check whether the field contains
             * email or username
             */
            if(str_contains($credentials['emailOrUsername'], '@')){
                $attempt['email'] = $credentials['emailOrUsername'];
            }else{
                $attempt['user_name'] = $credentials['emailOrUsername'];
            }

            //check for user's credentials
            if (!Auth::attempt($attempt, $remember)) {
                return $this->unprocessableApiResponse(Lang::get('core.invalidDetails'));
            }

            $user = Auth::user();

            User::where('user_id', $user->user_id)->update(['lastLoginDate' => Carbon::now()->format('Y-m-d H:i:s')]);
            $tokenResult = $user->createToken(env('APP_NAME'));
            $token = $tokenResult->token;
            $token->expires_at = Carbon::now()->addWeeks(1);
            $token->save();
            $success['token'] = $tokenResult->accessToken;
            $success['token_type'] = "Bearer";
            $success['expires_at'] = Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString();

            return response()->json($success, 200);
        } catch (\Throwable $th) {
            return $this->errorApiResponse($th->getMessage());
        }
    }
}
