<?php

namespace App\Http\Requests\csv;

use Illuminate\Foundation\Http\FormRequest;

class ImportVMapCsv extends FormRequest
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
            'name' => ['required', 'max:2048', 'mimes:csv,xlsx'],
        ];
    }
}
