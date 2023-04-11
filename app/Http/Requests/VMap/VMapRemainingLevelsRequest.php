<?php

namespace App\Http\Requests\VMap;

use Illuminate\Foundation\Http\FormRequest;

class VMapRemainingLevelsRequest extends FormRequest
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
            'delegateTo' => ['required', 'exists:users,user_id'],
            'name' => ['required']
        ];
    }
}
