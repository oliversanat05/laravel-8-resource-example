<?php

namespace App\Http\Requests\userRequest;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

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
            'email' => 'required|email|unique:users,email,' . $this->user . ',user_id,status, 1',
            'userName' => 'required|alpha_num|unique:users,user_name,' . $this->user . ',user_id',
            'timezone' => 'required',
            'role' => 'required',
            'assignedCoach' => 'required',
            'status' => 'required',
        ];
    }
}
