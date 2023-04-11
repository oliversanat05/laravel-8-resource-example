<?php

namespace App\Http\Requests\wowRequests;

use App\Traits\FailedValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class ClientGapAnalysisDataRequest extends FormRequest
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
            'listing_id' => 'required|exists:statement_listings,id',
            'scores.*.client_id' => 'required|exists:clients,id',
            'scores.*.score' => 'integer',
            'scores.*.gap_analysis_heading_id' => 'required|exists:gap_analysis_headings,id',
            'clients.*.id' => 'required|exists:clients,id',
            'clients.*.conversation_date' => 'nullable|date_format:Y-m-d',
        ];
    }

    public function messages()
    {
        return [
            'listing_id.exists' => 'Statement should exist in database',
            'scores.*.client_id.exists' => 'Client should exist in database',
            'scores.*.gap_analysis_heading_id.exists' => 'Invalid Gap Analysis heading ID !',
            'clients.*.id.exists' => 'Client should exist in database',
            'clients.*.conversation_date.date_format' => 'The date should be in MM-DD-YY format',
        ];
    }

}
