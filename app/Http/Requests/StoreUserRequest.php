<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users,email,NULL,user_id,deleted_at,NULL',
            'userName' => 'required|alpha_num|unique:users,user_name,NULL,user_id,deleted_at,NULL',
            'password' => 'required|min:6',
            'timezone' => 'required|min:1',
            'role' => 'required|min:1',
            'assignedCoach' => 'required|min:1',
            'additionalCoach' => 'nullable',
            'status' => 'required'
        ];
    }

    /**
     * Data get from request
     * 
     * @return array
     */

    public function userData(): array 
    {
        return [
            'name' => $this->get('firstName') . ' ' . $this->get('lastName'),
            'email' => $this->get('email'),
            'user_name' => $this->get('userName'),
            'password' => \Hash::make($this->get('password')),
            'status' => $this->get('status'),
            'dialNumber' => $this->get('meetingDialNumber'),
            'accessCode' => $this->get('meetingAccessCode'),
            'meetingLink' => $this->get('meetingLink'),
            'role_id' => $this->get('role')
        ];
    }

    /**
     * Data get from request
     * 
     * @return array
     */

    public function profileData($newUser): array 
    {
        $rangeStartDate = Carbon::now()->startOfYear()->toDateString();
        $rangeEndDate = Carbon::now()->endOfYear()->toDateString();

        return [
            'userId' =>  $newUser->user_id,
            'timeZoneId' => $this->get('timezone'),
            'firstname' => $this->get('firstName'),
            'lastname' => $this->get('lastName'),
            'email' => $this->get('email'),
            'rangeStartDate' => $rangeStartDate,
            'rangeEndDate' => $rangeEndDate
        ];
    }

     /**
     * Data get from request
     * 
     * @return array
     */

    public function coachData($newUser): array 
    {
        return [
            'clientUserId' => $newUser->user_id,
            'coachUserId' => $this->get('assignedCoach'),
            'additionalCoach' => $this->get('additionalCoach') ? implode(',', $this->get('additionalCoach')) : '',
        ];
    }
}
