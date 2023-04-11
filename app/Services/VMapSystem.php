<?php

namespace App\Services;
use App\Http\Controllers\Controller;
use App\Models\Succession\VMap;
use Auth;
use App\Models\Tracking\DelegateUser;
use App\Models\Profile\Profile;
use Request;
use App\Models\ENCQualifier;
use Config;
use DB;

class VMapSystem
{

    /**
     *
     * This function will get the vMap Dropdown list
     *
     * @param array
     * @return object
     */
    public static function vMapContents($includeRelation, $pageSize)
    {
        $status = config('statistics.vMapStatus');

        $vMap = VMap::where('userId', Auth::user()->user_id)->whereIn('isDelete', [1,-1])->with((isset($includeRelation[0]) && ($includeRelation[0] == 'values')) ? [$includeRelation[0] => function ($query) use($status, $includeRelation){

            $query->where('isDelete', true)->with((isset($includeRelation[1]) && ($includeRelation[1] == 'kpis')) ? [$includeRelation[1] => function ($query) use($status, $includeRelation) {

                $query->where('isDelete', false)->whereIn('statusId', $status)->with((isset($includeRelation[2]) && ($includeRelation[2] == 'strategy')) ? [$includeRelation[2] => function ($query) use($status, $includeRelation) {

                    $query->where('isDelete', false)->whereIn('statusId', $status)->with((isset($includeRelation[3]) && ($includeRelation[3] == 'project')) ? [$includeRelation[3] => function ($query) use($status, $includeRelation) {

                        $query->where('isDelete', false)->whereIn('statusId', $status)->with((isset($includeRelation[4]) && ($includeRelation[4] == 'criticalActivity')) ? [$includeRelation[4] => function ($query) use($status) {

                            $query->where('isDelete', false)->whereIn('statusId',$status)->orderBy('cOrder');

                        }] : [])->orderBy('pOrder');

                    }] : [])->orderBy('sOrder');

                }] : [])->orderBy('kOrder');

            }] : [])->orderBy('displayOrder');

        }] : [])->with('activityTitle')->orderBy('formTitle', 'ASC')->get()->toArray(); // vMap table

        return $vMap;
    }

    /**
     * This function will sync all the delegate into array from string.
     * @param NA
     * @return json
     */
    public static function getDelegate($delegate, $isQualifier=0){
        $dataArray  = array();
        if($delegate):
            $explode = explode(',',$delegate);
            foreach($explode AS $index => $value):
                array_push($dataArray, intval($value));
            endforeach;
        endif;
        return $dataArray;
    }



    /**
     * This function will get the active delegate list to avoid the new activity with inactive delegate
     * @param NA
     * @return object
     */
    public static function getActiveDelegate(){

        return DelegateUser::whereHas('user', function($q){
            $q->whereParentid(Auth::user()->user_id);
        })->with(['user:user_id,name'])->get();
    }


    /***
     * Create a function to get the vmap value drop down contents
     * In this function uses general class function
     * @param array
     * @return json
     */

    public function activeDelegatesStatus(){

        $dataArray      = array();
        $delegates      = self::getActiveDelegate();
        if($delegates){
            foreach($delegates AS $key=> $value){
                $mixArray   = array();
                $mixArray['value'] = $value->user->user_id;
                $mixArray['label'] = $value->user->name;
                $mixArray['status'] = $value->status;
                array_push($dataArray, $mixArray);
            }
        }
        return $dataArray;
    }

    /**
     *
     * this function will return the qualifier list
     * for the authenticated user
     *
     * @param NA
     * @return object
     */
    public function getUserQualifier($pageSize)
    {
        $delegate = Request::get('delegate');

        $authUser = (Auth::user()) ? Auth::user()->user_id : $delegate;

        return ENCQualifier::whereHas('user', function($query) use($authUser){
            $query->whereNotIn('role_id', [Config::get('statistics.delegateUserType')]);
        })->with(['user:name,user_id'])
        ->where('parentId', $authUser)
        ->paginate($pageSize);
    }

    /**
     * method used to get vMap list
     */
    public function vMapList() {
        return VMap::select('vMapId')->where('isDelete', '<>', 0)
            ->whereUserid(Auth::user()->user_id)->get();
    }
}
