<?php

namespace App\Http\Requests\vMapActionRequest;

use Illuminate\Foundation\Http\FormRequest;

class VMapUpdateRequest extends FormRequest
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
            // 'vmap' => ['required', 'exists:vMap,vMapId'],
            'formTitle' => ['required'],
            'formDate' => ['required', 'date_format:Y-m-d']
        ];
    }
}
