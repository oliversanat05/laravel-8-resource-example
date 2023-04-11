<?php

namespace App\Services\wowService\RelationalGridService;

use App\Models\wowModels\Ideas;
use App\Models\wowModels\RelationalGrid;
use DB;
use Illuminate\Support\Facades\Auth;

class RelationalGridService
{
    /**
     * Create a new Grid in context of user
     * @param $data
     * @return \object
     */
    public function createOrUpdateGrid($data)
    {
        $formattedData = [];
        try {
            DB::beginTransaction();
            foreach ($data['grids'] as $key => $value) {
                foreach ($value['tier'] as $key1 => $innerValue) {
                    $innerFormatData = [];
                    $innerFormatData['idea_id'] = $value['idea_id'];
                    $innerFormatData['responsible_person_id'] = $value['responsible_person_id'];
                    $innerFormatData['tier_id'] = $innerValue['tier_id'];
                    $innerFormatData['status'] = $innerValue['status'];
                    $innerFormatData['user_id'] = Auth::user()->user_id;
                    array_push($formattedData, $innerFormatData);
                }
            }
            $ideaIds = Ideas::where([
                ['user_id',Auth::user()->user_id],
                ['idea_type',$data['type']],
            ])->pluck('id');

            RelationalGrid::where('user_id',Auth::user()->user_id)->whereIn('idea_id',$ideaIds)->delete();
            RelationalGrid::upsert($formattedData, ['unique_grid_key'], ['responsible_person_id','status','idea_id']);
            DB::commit();
            return true;

        } catch (\Throwable$e) {
            DB::rollback();
            return false;
        }
    }

    /**
     * Create a new Grid in context of user
     * @param $data
     * @return \object
     */
    public function showParticularGrid($id)
    {
        $userId = Auth::user()->user_id;
        $particularGrid = RelationalGrid::where([['id', '=', $id], ['user_id', '=', $userId], ['deleted_at', '=', null]])->get();
        return $particularGrid;
    }

    /**
     * show all grids for a particular type
     *
     * @return \object
     */
    public function getAllGridService($idea_type)
    {
        $userId = Auth::user()->user_id;
        $particularGridType = RelationalGrid::with(['ideas'])->where([
            ['user_id', '=', $userId],
            ['deleted_at', '=', null]
            ])->whereRelation('ideas', 'idea_type', '=', $idea_type)
            ->get()->toArray();

        $formattedData = formatClientGridData($particularGridType);

        return $formattedData;
    }

    /**
     * Delete a particular grid
     *
     * @return \object
     */
    public function deleteGridService($relationalGridId)
    {
        $userId = Auth::user()->user_id;
        $deleteIdea = RelationalGrid::where([
            ['id', '=', $relationalGridId],
            ['user_id', '=', $userId],
        ])->delete();
        return $deleteIdea;
    }

    /**
     * Mass Delete Grids
     *
     * @return \object
     */
    public function massDeleteGridService($ids)
    {
        try {
            DB::beginTransaction();
            Ideas::whereIn('id', $ids)->where('is_default','!=',config('constants.metricScope.local'))->delete();
            RelationalGrid::whereIn('idea_id', $ids)->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
