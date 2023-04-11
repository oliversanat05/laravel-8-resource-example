<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Str;
use App\Models\User;
use App\Models\SsoIntegration;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use Lang;
use Auth;

class SSOClientController extends Controller
{

    use ApiResponse;

    /**
     * method used to login the user through SSO
     */
    public function login(Request $request){
        $request->session()->put('state', Str::random(40));

        $request->session()->put(
            'code_verifier', $code_verifier = Str::random(128)
        );

        $codeChallenge = strtr(rtrim(
            base64_encode(hash('sha256', $code_verifier, true))
        , '='), '+/', '-_');

        $query = http_build_query([
            'client_id' => env('SSO_CLIENT_ID', 7),
            'redirect_uri' => env('SSO_CALLBACK_URL', "https://staging.padapi.rubico.dev/callback"),
            'response_type' => env('SSO_RESPONSE_TYPE', "code"),
            'scope' => env('SSO_SCOPE', '*'),
            'state' => Str::random(40),
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => env('SSO_CODE_CHALLENGE_METHOD', 'S256')
        ]);
        return redirect(env('SSO_OAUTH_AUTHORIZE', 'https://app.proadvisorcoach.dev/oauth/authorize?') . $query);
    }

    /**
     * method used to receieve the parameters after callback
     */
    public function callback(Request $request, User $user){
        try{
            $state = $request->session()->pull('state');

            $codeVerifier = $request->session()->pull('code_verifier');

            $response = Http::asForm()->post(env('SSO_OAUTH_URL', 'https://app.proadvisorcoach.dev/oauth/token'), [
                'grant_type' => env('SSO_GRANT_TYPE', 'authorization_code'),
                'client_id' => env('SSO_CLIENT_ID', 7),
                'redirect_uri' => env('SSO_CALLBACK_URL', 'https://staging.padapi.rubico.dev/callback'),
                'code_verifier' => $codeVerifier,
                'code' => $request->code,
            ]);
            
            $response = $response->json();

            $token =  $response['access_token'];
            $token_parts = explode('.', $token);
            $token_header = $token_parts[1];
            $token_header_json = base64_decode($token_header);
            $token_header_array = json_decode($token_header_json, true);
            $token_id = $token_header_array['jti'];

            $ssoUserDetails = Http::withToken($token)
                                ->acceptJson()
                                ->get(env('SSO_OAUTH_USER_URL', 'https://app.proadvisorcoach.dev/api/auth/user'))->json();

            $user = $ssoUserDetails['data'];

            //store sso user data in the table
            SsoIntegration::create([
                'user_id' => $user['id'],
                'token_type' => $response['token_type'],
                'access_token' => $token,
                'refresh_token' => $response['refresh_token'],
                'expires_at' => Carbon::parse($response['expires_in'])->toDateTimeString(),
                'related_username' => $user['username'],
                'related_email' => $user['email'],
                'related_user_id' => $user['id'],
                'related_user_data' => json_encode($ssoUserDetails),
            ]);
            
            //getting the sso user email and check it's available on PAD database
            if(User::whereEmail($user['email'])->whereOr('user_name', $user['username'])->exists()){
                $user = User::whereEmail($user['email'])->whereOr('user_name', $user['username'])->first();
                if($user->status){
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

                    return response()->json($data, 200);
                }else{
                    return $this->unprocessableApiResponse(Lang::get('core.invalidDetails'));
                }   
            }else{
                return $this->unprocessableApiResponse(Lang::get('core.invalidCredentialsForPad'));
            }
        }catch(\Exception $ex){
            return $this->unprocessableApiResponse(Lang::get('core.unproccessableEntity'));
        }
    }
}
