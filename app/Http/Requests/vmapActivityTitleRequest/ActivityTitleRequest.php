<?php

namespace App\Http\Requests\vmapActivityTitleRequest;

use Illuminate\Foundation\Http\FormRequest;

class ActivityTitleRequest extends FormRequest
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
            // 'vMapId' => ['required', 'exists:vMap,vMapId'],
            'valueTitle' => ['required', 'min:1,max:50'],
            'kpiTitle' => ['required', 'min:1,max:50'],
            'strategyTitle' => ['required', 'min:1,max:50'],
            'projectTitle' => ['required', 'min:1,max:50'],
            'criticalActivityTitle' => ['required', 'min:1,max:50']
        ];
    }


}
