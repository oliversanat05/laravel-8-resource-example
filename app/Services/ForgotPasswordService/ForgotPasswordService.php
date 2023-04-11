<?php

namespace App\Services\ForgotPasswordService;

use App\Mail\ForgotPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Support\Facades\Mail;
use Str;

class ForgotPasswordService
{

    /**
     * for sending the forgot password email to the user
     *
     * @param $data
     * @return boolean
     */
    public function sendforgotPasswordMail($data)
    {

        $token = Str::random(64);

        $userData = [
            'email' => $data['email'],
            'token' => $token,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $userExists = User::whereEmail($data['email'])->exists();

        if ($userExists) {

            //check if the email exists in the password resets table
            $passwordResetMailExists = PasswordReset::where('email', $data['email'])->exists();

            $passwordReset = '';

            //if the user's email exists in the table update the token or else create the record with the user's email and the token
            if ($passwordResetMailExists) {
                /**
                 * used raw query as the default password resets table does not have a id column and the updating or inserting data using the eloquent requires the id column in the table
                 */
                $passwordReset = PasswordReset::whereEmail($data['email'])->update(['token' => $token]);
            } else {
                $passwordReset = PasswordReset::insert($userData);
            }

            //to send the email with the verification link
            Mail::to($data['email'])->send(new ForgotPasswordMail($token));

            DB::commit();

            return true;
        }

        return false;
    }

    /**
     * for resetting the password after the forgot password email is recieved
     *
     * @param $data
     * @return void
     */
    public function resetPassword($data)
    {
        /**
         * check for the token in password resets table and also check if the
         * token is not expired
         * Token expiry time => 1 hour
         */
        $checkTokenExistsAndNotExpired = PasswordReset::whereToken($data['token'])
            // ->where('created_at', '>', Carbon::now()->subHours(1))
            ->first();

        if ($checkTokenExistsAndNotExpired) {
            $updatePassword = User::whereEmail($checkTokenExistsAndNotExpired->email)->update(['password' => Hash::make($data['password'])]);

            if ($updatePassword) {

                //delete the record from password reset table after the password is updated
                return PasswordReset::whereToken($data['token'])->delete();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
