<?php

namespace App\Http\Controllers\Api\V1\Roles;

use Auth;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Access\RolePermission;
use App\Http\Requests\rolePermission\RolePermissionRequest;
use App\Traits\ApiResponse;
use DB;

class UserRoleController extends Controller
{
    use ApiResponse;

    /**
     * This function will get the roles
     * of the user
     *
     * @return JSON
     */
    public function index()
    {
        $roles = UserRole::getUserRoles();

        return $this->successApiResponse(__('core.roleDetailsFetched'), $roles);
    }

    /**
     * This function will count the users according to the roles
     */
    public function getUserCountByRole()
    {
        $role_user_count = User::countRoleUser();

        return $this->successApiResponse(__('core.roleCountDetails'), $role_user_count);
    }

    /**
     * method used to update role
     */
    public function update(Request $request){

        try {

            DB::beginTransaction();

            $access = RolePermission::createOrDelete($request->access);

            DB::commit();

            if($access) {
                return $this->successApiResponse(__('core.updateRole'));
            } else {
                return $this->unprocessableApiResponse('Error in updating page access');
            }

        } catch (\Throwable $th) {
            DB::rollback();
            return $this->errorApiResponse($th->getMessage());
        }

    }
}
