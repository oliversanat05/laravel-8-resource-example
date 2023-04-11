<?php

namespace App\Http\Requests\callmaximizerRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class CallMaximzierComponentRequest extends FormRequest {
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
			'params.*.callMaximizerId' => ['required'],
			'params.*.longComment' => ['sometimes'],
			'params.*.satisfactionLevel' => ['sometimes'],
			'params.*.motivation' => ['sometimes'],
			'params.*.updated' => ['required'],
		];
	}

	public function failedValidation(Validator $validation) {
		$response = new JsonResponse(
			[
				"success" => false,
				"message" => $validation->errors()->all(),
			],
			JsonResponse::HTTP_UNPROCESSABLE_ENTITY
		);

		throw new HttpResponseException($response);
	}
}
