<?php

namespace App\Http\Controllers\Api\V1\WowApi\MetricAreas;

use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\wowModels\MetricArea;
use Illuminate\Support\Facades\Auth;
use App\Models\wowModels\ClientMetricData;
use App\Http\Requests\wowRequests\StoreMetricAreaRequest;
use App\Http\Requests\wowRequests\UpdateMetricAreaRequest;
use App\Services\wowService\MetricAreaService\MetricAreaService;

class MetricAreaController extends Controller
{
    use ApiResponse;

    /**
     * Creating an instance of MetricAreaService
     * at begning
     */
    public function __construct()
    {
        $this->MetricAreas = new MetricAreaService();
    }

    /**
     * Display all default metrics and users metrics
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $allMetricsData = $this->MetricAreas->getAllMetrics();
            return $this->successApiResponse(__('wowCore.displayAllMetrics'), $allMetricsData);
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMetricAreaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMetricAreaRequest $request)
    {
      
        try {
            $data = $request->all();
            $data['is_default'] = config('constants.metricScope.local');
            //Creating a new Metric
            $createdMetric = $this->MetricAreas->createNewMetric($data);

            if ($createdMetric) return $this->successApiResponse(__('wowCore.metricSuccessCreate'), $createdMetric);
            else return $this->unprocessableApiResponse(__('wowCore.metricCreateError'));
        
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MetricArea  $metricId
     * @return \Illuminate\Http\Response
     */
    public function show($metricId)
    {
        try {
            $editedMetric = $this->MetricAreas->getSpecificMetrics($metricId);
            if ($editedMetric) return $this->successApiResponse(__('wowCore.specificMetric'), $editedMetric);
            else return $this->unprocessableApiResponse(__('wowCore.emptyResponse'));

        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMetricAreaRequest  $request
     * @param  \App\Models\MetricArea  $metricId
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMetricAreaRequest $request, $metricId)
    {
       
        try {
            // Checking whether user not trying to update the default Metric
            $metricArea = MetricArea::where('id', $metricId)->first();
            if ($metricArea && $metricArea['user_id'] != null) {

                // Updating Metric In database
                $updatedMetric = $this->MetricAreas->updateMetric($request->all(), $metricId);

                if ($updatedMetric) return $this->successApiResponse(__('wowCore.metricSuccessUpdate'), MetricArea::findOrFail($metricId));
                else return $this->unprocessableApiResponse(__('wowCore.metricUpdateError'));

            } elseif ($metricArea && $metricArea['user_id'] == null) {
                // Can't update a default metrics
                return $this->unprocessableApiResponse(__('wowCore.defaultMetricUpdateError'));
            }
            // Can't find the metric in DB
            return $this->unprocessableApiResponse(__('wowCore.defaultMetricdoesNotExistsError'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MetricArea  $metricId
     * @return \Illuminate\Http\Response
     */
    public function destroy($metricId)
    {
        try {
            # NOTE : Get the user_id from Auth
            $userId = Auth::user()->user_id;

            //checking whether it is used in any client metric data
            $isUsed=ClientMetricData::where('metric_id',$metricId)->where('deleted_at',NULL)->exists();

            if($isUsed){
                // Can't Delete the metrics which is being used in another 
                return $this->unprocessableApiResponse(__('wowCore.alreadyUsedMetricDeleteError'));
            }

            // Checking whether user not trying to update the default Metric
            $metricArea = MetricArea::where('id', $metricId)->first();

            if ($metricArea && $metricArea['user_id'] != null) {

                // Deleting Metric from a database
                $deletedMetric = $this->MetricAreas->deleteMetric($metricId);
                if ($deletedMetric) return $this->successApiResponse(__('wowCore.deleteMetricSucess'));
                else return $this->errorApiResponse(__('wowCore.internalServerError'));
                
            } elseif ($metricArea && $metricArea['user_id'] == null) {
                // Can't Delete the default metrics
                return $this->unprocessableApiResponse(__('wowCore.defaultMetricDeleteError'));
            }
            // Metrics doesn't exists
            return $this->unprocessableApiResponse(__('wowCore.defaultMetricdoesNotExistsError'));

        } catch (\Exception $e) {
            return $this->errorApiResponse($e);
        }

    }
}
