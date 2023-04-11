<?php

namespace App\Http\Requests\wowRequests;

use Illuminate\Foundation\Http\FormRequest;

class ClientMetricDataRequest extends FormRequest
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
            'listing_id' => 'required|exists:statement_listings,id',
            'metric_type' => 'required|in:1,2',
            'metric_id' => 'required|array|min:5|max:5|exists:metric_areas,id',
        ];
    }
}
