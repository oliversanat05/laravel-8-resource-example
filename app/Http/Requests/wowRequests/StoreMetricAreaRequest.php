<?php

namespace App\Http\Requests\wowRequests;

use App\Traits\FailedValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Rules\IsMetricTitleUniqueForUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class StoreMetricAreaRequest extends FormRequest
{
    use FailedValidation;
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
            'metric_type' => 'required|in:1,2',
            'title' => [
                'required',
                 new IsMetricTitleUniqueForUser($this->title),
            ],
            'user_id' => 'required|exists:users,user_id'
        ];
    }

}
