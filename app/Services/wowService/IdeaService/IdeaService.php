<?php

namespace App\Services\wowService\IdeaService;

use App\Models\wowModels\Ideas;
use App\Models\wowModels\RelationalGrid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IdeaService
{

    /**
     * Fetch all the Ideas from the DB
     *
     * @return \object
     */
    public function getAllIdea($idea_type)
    {
        $userId = Auth::user()->user_id;
        $allService = Ideas::where(function ($query) use ($userId) {
            $query->where('user_id', '=', $userId)
                ->orWhere('is_default', '=', config('constants.isIdea.global'));
        })->where([['idea_type', '=', $idea_type], ['deleted_at', '=', null]])->get();
        return $allService;

    }

    /**
     * Create a new Idea in Idea
     * @param $data
     * consists of metric_type, is_default , title and user_id [optional]
     * @return \object
     */
    public function createNewIdea($data)
    {
        try {
            if (Auth::user()->role_id != config('constants.adminRoleId')) {
                $data['is_default'] = config('constants.isIdea.local');
            }
            //Creating a new Idea in DB
            $data['user_id'] = Auth::user()->user_id;
            $createdIdeas = Ideas::create($data);
            return $createdIdeas;
        } catch (\Exception$e) {
            throw ValidationException::withMessages(['failed' => 'Something went wrong']);
        }
    }

    /**
     * Showing a particular ideas
     *
     * @return \object
     */
    public function getParticularIdea($idea_id)
    {
        $userId = Auth::user()->user_id;
        $particularIdea = Ideas::where(function ($query) use ($userId) {
            $query->where('user_id', '=', $userId)
                ->orWhere('is_default', '=', config('constants.isIdea.global'));
        })->where([['id', '=', $idea_id], ['deleted_at', '=', null]])->get();
        return $particularIdea;

    }

    /**
     * Showing a particular ideas
     *
     * @return \object
     */
    public function updateParticularIdea($data, $idea_id)
    {
        $userId = Auth::user()->user_id;
        $updatedIdea = Ideas::where([
            ['id', '=', $idea_id],
            ['user_id', '=', $userId],
            ['deleted_at', '=', null],
        ])->update($data);

        if ($updatedIdea) {
            return true;
        }
        return false;
    }

    /**
     * Delete a particular ideas
     *
     * @return \object
     */
    public function getTierIdeas()
    {
        $userId = Auth::user()->user_id;
        $data = Ideas::with(['relationalGrid.tier:id,tier_type','relationalGrid:id,tier_id,idea_id,status'])
                ->where('user_id',$userId)
                ->whereRelation('relationalGrid', 'idea_id', '!=', null)
                ->get(['id','idea_type','idea_title'])
                ->toArray();
        $formatedData = formatIdeaDataWowTracker($data);
        return $formatedData;
    }



    /**
     * Delete a particular ideas
     *
     * @return \object
     */
    public function deleteIdea($idea_id)
    {
        $userId = Auth::user()->user_id;

        $deleteIdea = RelationalGrid::where([
            ['idea_id', '=', $idea_id],
            ['user_id', '=', $userId],
        ])->delete();

        $deleteIdea = Ideas::where([
            ['id', '=', $idea_id],
            ['user_id', '=', $userId],
        ])->delete();

        return $deleteIdea;
    }

}
