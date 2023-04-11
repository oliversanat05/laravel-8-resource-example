<?php

namespace App\Http\Requests\vMapActionRequest;

use Carbon\Carbon;
use Config;
use Illuminate\Foundation\Http\FormRequest;

class VMapCopyRequest extends FormRequest {
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
		return [
			'formDate' => ['required'],
			'formTitle' => ['required', 'max:45'],
			'delegate' => ['required'],
		];
	}

	/**
	 * This function will get the vmap
	 * data from request
	 * @param NA
	 * @return array
	 */
	public function copyData() {
		return [
			'formDate' => Carbon::parse($this->formDate)->format(Config::get('constants.dbDateFormat')),
			'formTitle' => $this->formTitle,
			'delegate' => ($this->delegate > 0) ? $this->delegate : null,
			'vMapId' => $this->vMapId,
		];
	}
}
