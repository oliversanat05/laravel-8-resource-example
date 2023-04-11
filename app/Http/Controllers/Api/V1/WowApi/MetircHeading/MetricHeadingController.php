<?php

namespace App\Http\Controllers\Api\V1\WowApi\MetircHeading;

use App\Http\Controllers\Controller;
use App\Models\wowModels\MetricHeading;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MetricHeadingController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $metricHeadings = MetricHeading::get(['id', 'heading', 'metric_type'])->toArray();
            if (!$metricHeadings) {
                return $this->unprocessableApiResponse(__('wowCore.cantFetcHeading'));
            }
            return $this->successApiResponse(__('wowCore.metricHeading'), $metricHeadings);
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if ($id == config('constants.metricType.DEMOGRAPHIC') || $id == config('constants.metricType.PSYCHOGRAPHIC')) {
            try {
                $metricHeadings = MetricHeading::where('metric_type', $id)->get(['id', 'heading', 'metric_type'])->toArray();
                if (!$metricHeadings) {
                    return $this->unprocessableApiResponse(__('wowCore.wrongMetricType'));
                }
                return $this->successApiResponse(__('wowCore.metricHeading'), $metricHeadings);
            } catch (\Exception $e) {
                return $this->errorApiResponse(__('wowCore.internalServerError'));
            }
        } else {
            return $this->unprocessableApiResponse(__('wowCore.wrongMetricType'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
