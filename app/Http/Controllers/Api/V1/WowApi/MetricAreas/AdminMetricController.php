<?php

namespace App\Http\Controllers\Api\V1\WowApi\MetricAreas;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\wowModels\MetricArea;
use Illuminate\Support\Facades\Auth;
use App\Models\wowModels\ClientMetricData;
use App\Http\Requests\wowRequests\AdminStoreMetricAreaRequest;
use App\Services\wowService\MetricAreaService\MetricAreaService;

class AdminMetricController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $allAdminMetricsData = $this->MetricAreas->getAllAdminMetrics();
            return $this->successApiResponse(__('wowCore.displayAllMetrics'), $allAdminMetricsData);
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminStoreMetricAreaRequest $request)
    {
        try {
            $data = $request->all();
            $data['is_default'] = config('constants.metricScope.global');
            //Creating a new Metric
            $createdMetric = $this->MetricAreas->createNewMetric($data);

            if ($createdMetric) return $this->successApiResponse(__('wowCore.metricSuccessCreate'), $createdMetric);
            else return $this->unprocessableApiResponse(__('wowCore.metricCreateError'));

        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AdminStoreMetricAreaRequest $request, $metricId)
    {
        try {
            // Checking whether admin not trying to update the user Metric
            $metricArea = MetricArea::where('id', $metricId)->first();
            if ($metricArea && $metricArea['user_id'] == null) {
                // Updating Metric In database
                $updatedMetric = $this->MetricAreas->updateMetric($request->all(), $metricId);
                
                if ($updatedMetric) return $this->successApiResponse(__('wowCore.metricSuccessUpdate'), MetricArea::findOrFail($metricId));
                else return $this->unprocessableApiResponse(__('wowCore.metricUpdateError'));

            } elseif ($metricArea && $metricArea['user_id'] != null) {
                // Can't update a default metrics
                return $this->unprocessableApiResponse(__('wowCore.adminUpdateUserMetric'));
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($metricId)
    {
        try {

            //checking whether it is used in any client metric data
            $isUsed=ClientMetricData::where('metric_id',$metricId)->exists();
            if($isUsed){
                // Can't Delete the metrics which is being used in another 
                return $this->unprocessableApiResponse(__('wowCore.alreadyUsedMetricDeleteError'));
            }

            $metricArea = MetricArea::where('id', $metricId)->first();
            // Checking whether admin not trying to update the user Metric
            if ($metricArea && $metricArea['user_id'] == null) {
                // Deleting Metric from a database
                $deletedMetric = $this->MetricAreas->deleteMetric($metricId);
                
                if ($deletedMetric) return $this->successApiResponse(__('wowCore.deleteMetricSucess'));
                else return $this->errorApiResponse(__('wowCore.internalServerError'));

            } elseif ($metricArea && $metricArea['user_id'] != null) {
                // Can't Delete the user metrics
                return $this->unprocessableApiResponse(__('wowCore.userMetricDeleteError'));
            }
            // Metrics doesn't exists
            return $this->unprocessableApiResponse(__('wowCore.defaultMetricdoesNotExistsError'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }
}
