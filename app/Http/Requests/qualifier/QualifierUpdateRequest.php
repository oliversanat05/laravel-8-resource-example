<?php

namespace App\Http\Requests\qualifier;

use Illuminate\Foundation\Http\FormRequest;

class QualifierUpdateRequest extends FormRequest
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
            'name' => ['nullable', 'unique:users,name,'.$this->vmap_qualifier. ',user_id']
        ];
    }
}
