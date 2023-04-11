<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Profile\Profile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\settings\GeneralSettingsRequest;

class GeneralSettingsController extends Controller
{

    use ApiResponse;

    public function __construct()
    {

    }
    /**
   * this function will update the logged in user' general settings
   *
   * @param Request $request
   * @return void
   */
  public function update(GeneralSettingsRequest $request)
  {
      try {
          $param = $request->all();

          $defaultProfile = $param['defaultProfile'];

          unset($param['defaultProfile']);

          $newSettings = Profile::where('userId', Auth::user()->user_id)->update($request->generalSettings());

          $response = User::where('user_id', Auth::user()->user_id)->update($request->updateDefaultProfile());

          if($response){
              return $this->successApiResponse(__('core.settingsSuccess'));
          }else{
              return $this->unprocessableApiResponse(__('core.settingsSuccess'));
          }
      } catch (\Throwable $th) {

        throw $th;
        // return $th->getMes;
          return $this->errorApiResponse(__('core.internalServerError'));
      }

  }
}
