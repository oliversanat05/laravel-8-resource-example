<?php

namespace App\Traits;
use Symfony\Component\HttpFoundation\Response;
use Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

trait FailedValidation {

    public function failedValidation( Validator $validation) {
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
