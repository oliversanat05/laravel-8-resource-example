<?php

namespace App\Services\wowService\MetricAreaService;
use Illuminate\Validation\ValidationException;
use App\Models\wowModels\MetricArea;
use App\Models\wowModels\MetricHeading;
use Illuminate\Support\Facades\DB;
use App\Services\wowService\MetricAreaService\MetricheadingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MetricAreaService
{
    /**
     * Fetch all the admin metrics from DB 
     *
     * @return \object
     */
    public function getAllAdminMetrics()
    {
        $allAdminMetricsData = MetricArea::where('user_id', '=', null)->orderBy('id','DESC')->get();
        return $allAdminMetricsData;
    }

    /**
     * Fetch all the metrics from DB for user
     *
     * @return \object
     */
    public function getAllMetrics()
    {
        //Once User table has implemeted the userID should be fetched from AUTH
        $userId = Auth::user()->user_id;
        $allMetricsData = MetricArea::where('user_id', '=', null)
                    ->orWhere('user_id', '=', $userId)->orderBy('id','DESC')->get();
           
        return $allMetricsData;
    }

    /**
     * Get a specific Metric from Database
     * @param MetricArea $metric_id
     * @return \object
     */
    public function getSpecificMetrics($metricId)
    {
        //Once User table has implemeted the userID should be fetched from AUTH
        $userId = Auth::user()->user_id;

        $specificMetricsData = MetricArea::where(function ($query) use ($userId) {
                $query->where('user_id', '=', null)
                    ->orWhere('user_id', '=', $userId);
            })->where('id', $metricId)->first();

        //will encrypt metricId 
        //$specificMetricsData = MetricArea::where('id', $metricId)->first();

        return $specificMetricsData;
    }

    /**
     * Create a new metric in METRIC_AREAS
     * @param $data
     * consists of metric_type, is_default , title and user_id [optional]
     * @return \object
     */
    public function createNewMetric($data)
    {
        try {
            //Creating a new metric in DB
            $createdMetric = MetricArea::create($data);
                    
            return $createdMetric;
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['failed' => 'Something went wrong']);
        }
        
    }
    
    /**
     * Updating a particular metric in Metric_area
     * 
     * @param $Metric 
     * consists of metric_type, is_default , title and user_id [optional]
     * @return \object
     */
    public function updateMetric($data,$metricId)
    {
        $updatedMetric = MetricArea::where('id', $metricId)->update($data);   
        return $updatedMetric;          
    } 


    /**
     * Deleting a particular metric in Metric_area
     * 
     * @param $metricID 
     * consists of metric_type, is_default , title and user_id [optional]
     * @return \object
     */
    public function deleteMetric($metricId)
    {
        $deletedMetric = MetricArea::where('id', $metricId)->delete();
        return $deletedMetric;  
    } 
  
}
