<?php

namespace App\Http\Requests\wowRequests;

use App\Traits\FailedValidation;
use Illuminate\Http\JsonResponse;
use App\Rules\IsGridTitleUniqueForUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RelationalGridCreateRequest extends FormRequest
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
            'grids.*.idea_id' => 'required|exists:ideas,id',
            'grids.*.responsible_person_id' => 'required|exists:responsible_people,id',
            'grids.*.tier.*.tier_id' => 'required|exists:tiers,id',
            'grids.*.tier.*.status'=>'required|in:0,1'
        ];
    }

    /**
     * Show the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'grids.*.idea_id.exists' => 'Wow Idea should exist in database',
            'grids.*.idea_id.required' => 'Service or marketing idea is required!!!',
            'grids.*.responsible_person_id.required' => 'Responsible person is required!!!',
            'grids.*.responsible_person_id.exists' => 'Responsible person should exist in database!!!',
            'grids.*.tier.*.tier_id.exists' => 'Tier should exist in database',
            'grids.*.tier.*.status.required' => 'Service and marketing status is required!!!',
        ];
    }




}
