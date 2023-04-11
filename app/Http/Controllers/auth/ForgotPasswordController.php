<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\authRequest\ForgotPasswordRequest;
use App\Http\Requests\authRequest\ResetPasswordRequest;
use App\Services\ForgotPasswordService\ForgotPasswordService;
use App\Traits\ApiResponse;
use DB;

class ForgotPasswordController extends Controller
{

    use ApiResponse;

    protected $forgotPassword;

    public function __construct()
    {
        $this->forgotPassword = new ForgotPasswordService();
    }
    /**
     * for sending the forgot password link to the user
     *
     * @param ForgotPasswordRequest $request
     * @return void
     */
    public function resetPassword(ForgotPasswordRequest $request)
    {
        try {

            $data = $request->all();
            $sendEmail = $this->forgotPassword->sendforgotPasswordMail($data);

            if ($sendEmail) {
                return $this->successApiResponse(__('core.forgotPasswordMailSuccess'));

            } else {
                return $this->unprocessableApiResponse(__('wowCore.usersError'));
            }
        } catch (\Throwable$th) {
            DB::rollback();
            return $this->errorApiResponse($th->getMessage());
        }
    }

    /**
     * resetting the password after recieving the forgot password mail
     *
     * @param ResetPasswordRequest $request
     * @param String $token
     * @return void
     */
    public function updatePassword(ResetPasswordRequest $request)
    {

        try {
            DB::beginTransaction();

            $data = $request->all();
            $updatePassword = $this->forgotPassword->resetPassword($data);

            DB::commit();

            if ($updatePassword) {
                return $this->successApiResponse(__('core.forgotPasswordSuccess'));
            } else {
                return $this->unprocessableApiResponse(__('core.forgotPasswordError'));
            }
        } catch (\Throwable$th) {
            DB::rollback();
            return $this->errorApiResponse(__('core.internalServerError'));
        }

    }
}
