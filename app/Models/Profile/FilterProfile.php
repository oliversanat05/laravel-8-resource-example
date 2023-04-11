<?php

namespace App\Models\Profile;

use Auth;
use Config;
use Carbon\Carbon;
use App\Models\Succession\VMap;
use App\Models\Succession\Value;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FilterProfile extends Model
{
    use HasFactory;

    protected $table = 'filterProfile';
    public $primaryKey = 'filter_id';

    protected $fillable = ['filter_name', 'begining_date', 'ending_date', 'tracking_begining_date', 'tracking_ending_date'];

    /**
     * static method to create profile
     */
    public static function createOrUpdateProfile($request, $id = null){
        $parentId = 0;
        foreach($request['vmaps'] as $order => $vmap){
            if(isset($vmap['filter_id']) && $vmap['filter_id']){
                $filterData = [];
                $filterData['filter_name']  = $request['name'];
                $filterData['vMap_id']  = $vmap['vmapId'];
                $filterData['value_id']  = isset($vmap['valueIds'])?implode(',',$vmap['valueIds']):0;
                $filterData['kpi_id']  = isset($vmap['kpiIds'])?implode(',',$vmap['kpiIds']):0;
                $filterData['strategy_id']  = isset($vmap['strategyIds'])?implode(',',$vmap['strategyIds']):0;
                $filterData['project_id']  = isset($vmap['projectIds'])?implode(',',$vmap['projectIds']):0;
                $filterData['critical_activity_id']  = isset($vmap['criticalActivityIds'])?implode(',',$vmap['criticalActivityIds']):0;
                $filterData['delegate_id']= isset($vmap['delegateIds'])?implode(',',$vmap['delegateIds']):0;
                $filterData['display_order'] = $order;
                $filterData['begining_date'] = !empty($vmap['dialDateRange']) ? Carbon::parse($vmap['dialDateRange'][0])->format(Config::get('constants.dbDateFormat')) : Carbon::now()->format('Y-m-d');
                $filterData['ending_date']    = !empty($vmap['dialDateRange']) ? Carbon::parse($vmap['dialDateRange'][1])->format(Config::get('constants.dbDateFormat')) : Carbon::now()->format('Y-m-d');
                $filterData['tracking_begining_date']   = !empty($vmap['trackingDateRange']) ? Carbon::parse($vmap['trackingDateRange'][0])->format(Config::get('constants.dbDateFormat')) : Carbon::now()->format('Y-m-d');
                $filterData['tracking_ending_date']     = !empty($vmap['trackingDateRange']) ? Carbon::parse($vmap['trackingDateRange'][1])->format(Config::get('constants.dbDateFormat')) : Carbon::now()->format('Y-m-d');
                $filterData = FilterProfile::whereFilterId($vmap['filter_id'])->update($filterData);
                if($parentId == 0)
                    $parentId = $id;
            }else{
                $filterData = new FilterProfile();
                $filterData->filter_name  = $request['name'];
                $filterData->filter_parent_id  = $parentId;
                $filterData->user_id  = Auth::user()->user_id;
                $filterData->vMap_id  = $vmap['vmapId'];
                $filterData->value_id  = isset($vmap['valueIds'])?implode(',',$vmap['valueIds']):0;
                $filterData->kpi_id  = isset($vmap['kpiIds'])?implode(',',$vmap['kpiIds']):0;
                $filterData->strategy_id  = isset($vmap['strategyIds'])?implode(',',$vmap['strategyIds']):0;
                $filterData->project_id  = isset($vmap['projectIds'])?implode(',',$vmap['projectIds']):0;
                $filterData->critical_activity_id  = isset($vmap['criticalActivityIds'])?implode(',',$vmap['criticalActivityIds']):0;
                $filterData->delegate_id  = isset($vmap['delegateIds'])?implode(',',$vmap['delegateIds']):0;
                $filterData->display_order = $order;
                $filterData['begining_date'] = !empty($vmap['dialDateRange']) ? Carbon::parse($vmap['dialDateRange'][0])->format(Config::get('constants.dbDateFormat')) : Carbon::now()->format('Y-m-d');
                $filterData['ending_date'] = !empty($vmap['dialDateRange']) ? Carbon::parse($vmap['dialDateRange'][1])->format(Config::get('constants.dbDateFormat')) : Carbon::now()->format('Y-m-d');
                $filterData['tracking_begining_date'] = !empty($vmap['trackingDateRange']) ? Carbon::parse($vmap['trackingDateRange'][0])->format(Config::get('constants.dbDateFormat')) : Carbon::now()->format('Y-m-d');
                $filterData['tracking_ending_date'] = !empty($vmap['trackingDateRange']) ? Carbon::parse($vmap['trackingDateRange'][1])->format(Config::get('constants.dbDateFormat')) : Carbon::now()->format('Y-m-d');
                $filterData->save();
                if ($parentId == 0)
                    $parentId = $filterData->filter_id;
            }
        }
        return $filterData;
    }

    /**
     * static method to get the filter name
     */
    public static function getFilterName($id){
        return self::whereFilterId($id)->pluck('filter_name')->first();
    }

    /**
     * static method to get the profile by filter id
     */
    public static function getVmapByFilterId($id){
        return self::whereFilterId($id)->orWhere('filter_parent_id', $id)->with(['vmaps'])->get()->toArray();
    }

    /**
     * static method to get the unique profile by filter
     */
    public static function getUniqueProfileFilters($request, $id){
        return self::whereFilterName($request['name'])->whereUserId(Auth::user()->user_id)
                ->where(function($query) use($id) {
                    $query->where('filter_id', '!=', $id);
                    $query->where('filter_parent_id', '!=', $id);
                })->count();
    }

    /**
     * static method to count the vmaps of the profile user
     */
    public static function getCountVmaps($id){
        return self::whereFilterId($id)->orWhere('filter_parent_id', $id)->count();
    }

    /**
     * static method to delete the vmap of the profile user
     */
    public static function deleteVmap($id){
        return self::whereUserId(Auth::user()->user_id)->whereFilterId($id)->delete();
    }

    /**
     * static method to delete the profile
     */
    public static function deleteProfile($id){
        return self::whereUserId(Auth::user()->user_id)->whereFilterId($id)->orWhere('filter_parent_id', $id)->delete();
    }

    /**
     * Get all of the vmaps for the FilterProfile
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vmaps()
    {
        return $this->hasMany(VMap::class, 'vMapId', 'vMap_id');
    }

    /**
     * Get all of the values for the FilterProfile
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values()
    {
        return $this->hasMany(Value::class, 'valueId', 'value_id');
    }
}
