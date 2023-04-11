<?php

namespace App\Http\Requests\vMapActionRequest;

use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class VMapActionRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
        $rules['formDate'] = ['required', 'date_format:Y-m-d'];
        $rules['formTitle'] = ['required', 'max:45', 'unique:vMap,formTitle,true,isDelete,userId,' . Auth::user()->user_id];
		return $rules;
	}

	/**
	 * get the vmap request data
	 */
	public function vMapData() {
		return [
			'formDate' => Carbon::parse($this->formDate)->format('Y-m-d'),
			'formTitle' => $this->formTitle,
			'id' => isset($this->vmapId) ? $this->vmapId : '',
			'visionStatement' => isset($this->visionStatement) ? $this->visionStatement : null,
			'missionStatement' => isset($this->missionStatement) ? $this->missionStatement : null,
		];
	}

    public function messages()
    {
        return [
            'vmapId.required' => 'The vmap id is required for updating the vmap'
        ];
    }
}
