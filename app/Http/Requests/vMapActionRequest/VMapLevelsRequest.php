<?php

namespace App\Http\Requests\vMapActionRequest;

use Config;
use Illuminate\Foundation\Http\FormRequest;

class VMapLevelsRequest extends FormRequest {
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
		$rules['name'] = ['required', 'max:255'];
		$data['formTitle'] = $this->name;

		//check if the level is not the level1
		if ($this->type != 'level1') {
			$data['description'] = $this->activityDescription($this->description);
			$rules['description'] = ['max:' . Config::get('statistics.editorMaxLength')];
		}

		return $rules;
	}

	/***
	     * this function will filter the activity description
	     * @param Request
	     * @return json
*/
	public function activityDescription($description) {
		return $description;
	}
}
