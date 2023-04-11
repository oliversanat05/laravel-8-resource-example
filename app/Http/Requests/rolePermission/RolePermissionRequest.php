<?php

namespace App\Http\Requests\rolePermission;

use Illuminate\Foundation\Http\FormRequest;

class RolePermissionRequest extends FormRequest
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
            'page_id' => ['required', 'exists:pages,id'],
            'role_id' => ['required', 'exists:userRole,userRoleId'],
        ];
    }
}
