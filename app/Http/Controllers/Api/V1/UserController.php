<?php

namespace App\Http\Controllers\Api\V1;

use Auth;
use App\Models\User;
use App\Models\ManageUser;
use App\Models\ClientCoach;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Profile\Profile;
use App\Models\Timezone;
use App\Http\Controllers\Controller;
use App\Models\Tracking\DelegateUser;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Http\Requests\userRequest\UserUpdateRequest;
use App\Http\Requests\settings\GeneralSettingsRequest;
use DB;
class UserController extends Controller
{
  use ApiResponse;

  public $validRole;

  public function __construct()
  {
    $this->validRole =  [4,3,6,2];
  }

  /**
   * method used to display list of users.
   */
  public function index(Request $request){
    try{
        $params = $request->all();
        
        $pageSize = $request->query('pageSize');
        if( $params['parent'] > 0){
            $users = ClientCoach::usersByParent($params, $pageSize);
        }
        else if($params){
            $users = User::usersByFilters($params, $pageSize);
        }
        else{
            $users = User::allSuperUsers($pageSize);
        }

        return new UserCollection($users);
    }catch(\Exception $e){

        return $this->errorApiResponse(__('core.usersError'));
    }
  }

  /**
   * method used to get details of a user
   */
  public function show($id){
    $details = User::userDetails($id);

    return $this->successApiResponse(__('core.users'), new UserResource($details));
  }

  /**
   * user store
   */
  public function store(StoreUserRequest $request){
      try{
        $newUser = User::create($request->userData());
        Profile::create($request->profileData($newUser));
        ClientCoach::create($request->coachData($newUser));

        $this->createNewDelegateIfNotExist($newUser->user_id);

        return $this->successApiResponse(__('core.profileSuccess'), new UserResource($newUser));
      }catch(\Exception $e){
        return $this->errorApiResponse(__('core.profileError'));
      }
  }

  /**
   * It will add a default delegate with Logged user id if there is no delegate available.
   *
   */
  public function createNewDelegateIfNotExist($userId) {
      $delegate = DelegateUser::leftJoin('users', 'users.user_id','delegateUsers.userId')
          ->where('parentId', $userId);
      if (!$delegate->count()) {
          $defaultDelgateUser = new DelegateUser();
          $defaultDelgateUser->userId = $userId;
          $defaultDelgateUser->parentId = $userId;
          $defaultDelgateUser->isDelegate = 1;

          $defaultDelgateUser->save();
      }
  }

  /**
   * Update User
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
   */
  public function update(UserUpdateRequest $request, $id)
  {
    try {
      $input = $request->all();

      $user = [
        'name' => ($input['firstName'] . ' ' . $input['lastName']),
        'email' => $input['email'],
        'user_name' => $input['userName'],
        'status' => $input['status'],
        'dialNumber' => $input['meetingDialNumber'],
        'accessCode' => $input['meetingAccessCode'],
        'meetingLink' => $input['meetingLink'],
        'role_id' => $input['role']
      ];

      if (! empty($input['password'])) {
        $user['password'] = \Hash::make($input['password']);
      }

      User::where('user_id', $id)->update($user);

      $profile = [
        'timeZoneId' => $input['timezone'],
        'firstname' => $input['firstName'],
        'lastname' => $input['lastName'],
        'email' => $input['email']
      ];

      Profile::where('userId', $id)->update($profile);

      $coach = [
        'coachUserId' => $input['assignedCoach'],
        'additionalCoach' => ($input['additionalCoach']) ? implode(',', $input['additionalCoach']) : '',
      ];

      // Update Coach Data
      ClientCoach::where('clientUserId', $id)->update($coach);

      return $this->successApiResponse(__('core.profileSuccess'));
    } catch (\Exception $e) {

      return $this->unprocessableApiResponse(__('core.profileNotSave'));
    }
  }

  /**
   * Remove User
   * @param $id
   * @return mixed
   */
  public function destroy($id)
  {
    try {
      $user = User::where('user_id', $id)->withCount(['children'  => function($query) {
        $query->whereHas('user', function($chidQuery) {
          $chidQuery->whereNull('deleted_at');
        });
      }])
      ->first();

      if ($user && $user->children_count == 0) {
        $user->status = 0;
        $user->save();
        $user->delete();
        return $this->successApiResponse(__('core.deleteSuccess'));
      }else{
        return $this->errorApiResponse(__('core.deleteFailed'));
      }
    } catch (Exception $e) {
      return $this->errorApiResponse(__('core.deleteFailed'));
    }

  }



  /**
   * method used to count the users by role id
   */
  public function usersCount(Request $request){
      $roleId = $request->get('role');
      $userCount = User::userCountByRole($roleId);

      return response()->json(['status' =>  true, 'data' => $userCount], 200);
  }

  public function countRoleUser()
  {
      $userCount = User::countRoleUser();
      return response()->json(['status' =>  true, 'data' => $userCount], 200);
  }

  /**
     * Fetch user details
     * @param $id
     * @return mixed
     */
    function getUserData($id)
    {

        $userData = User::with(['coach' => function($query){
            $query->select('clientUserId','coachUserId','additionalCoach');
        }, 'profile' => function($qry){
            $qry->select('userId','timeZoneId', 'firstName', 'lastName');
        }])
        ->select('user_id','name', 'email', 'status', 'user_name', 'dialNumber', 'accessCode', 'meetingLink', 'role_id')
        ->where('user_id', '=', $id)
        ->withCount(['children'  => function($query) {
          $query->whereHas('user', function($chidQuery) {
            $chidQuery->whereNull('deleted_at');
          });
        }])
        ->first();

        if ($userData) {
          $userData->first_name = mb_convert_encoding(data_get($userData, 'profile.firstName'), 'UTF-8', 'HTML-ENTITIES');
          $userData->last_name = mb_convert_encoding(data_get($userData, 'profile.lastName'), 'UTF-8', 'HTML-ENTITIES');
          return response()->json($userData);
        }

        abort(404);
    }

    /**
     * Fetch Timezones
     * @return array
     */
    function timezones()
    {

        $timezones = Timezone::select('timeZoneId as key', 'timeZoneId as value', 'longDesc as label')->orderBy('displayOrder')->get();

        return response()->json($timezones);
    }

    /**
     * Fetch Timezones
     * @return array
     */
    function roles()
    {

        $roles = Roles::select('userRoleId as key', 'userRoleId as value', 'description as label')->where('canLogin',true)->orderBy('sortOrder')->get();

        return response()->json($roles);
    }

    /**
     * Fetch Assigned Coaches
     * @param $coachId
     * @return \Illuminate\Http\JsonResponse
     */
    function assignedCoaches($coachId)
    {
      $userData = User::with([
        'role' => function($roleQuery) {
          $roleQuery->select('userRoleId');
        },
        'profile' => function($profileQuery) {
          $profileQuery->select('userId', 'userId as value', DB::raw("CONCAT(firstName,' ', lastName) AS label"));
          $profileQuery->orderBy('firstName');
          $profileQuery->orderBy('lastName');
        }
      ])
      ->has('profile')
      ->select('user_id', 'role_id')
      ->whereIn('role_id', $this->validRole)
      ->orderBy('name', 'ASC')
      ->get()
      ->pluck('profile');
      return response()->json($userData);
    }

    /**
     * Fetch searched Assigned Coaches
     * @param $coachId
     * @return \Illuminate\Http\JsonResponse
     */
     function searchAssignedCoaches($coachId, $keyword) {
       $userData = User::with([
         'role' => function($roleQuery){
           $roleQuery->select('userRoleId');
         },
         'profile' => function($profileQuery){
           $profileQuery->select('userId as value', DB::raw("CONCAT(firstName,' ', lastName) AS label"), 'userId', 'userId as key');
           $profileQuery->orderBy('firstName');
           $profileQuery->orderBy('lastName');
         }
       ])
       ->select('user_id', 'role_id')
       ->where('name', 'LIKE', '%'.$keyword.'%')
       ->where('user_id', '!=', $coachId)
       ->whereNotIn('role_id', $this->validRole)
       ->limit(500)
       ->get()
       ->pluck('profile');
       return response()->json($userData);
     }
}
