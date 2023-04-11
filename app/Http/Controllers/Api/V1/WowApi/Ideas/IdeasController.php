<?php

namespace App\Http\Controllers\Api\V1\WowApi\Ideas;

use App\Http\Controllers\Controller;
use App\Http\Requests\wowRequests\IdeaCreateRequest;
use App\Http\Requests\wowRequests\IdeaTypeValidator;
use App\Models\wowModels\Ideas;
use App\Models\wowModels\RelationalGrid;
use App\Models\wowModels\WowTracker;
use App\Services\wowService\IdeaService\IdeaService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IdeasController extends Controller
{
    use ApiResponse;
    /**
     * Creating an instance of IdeaService
     * at begning
     */
    public function __construct()
    {
        $this->ideaService = new IdeaService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IdeaTypeValidator $request)
    {
        try {

            $AllIdea = $this->ideaService->getAllIdea($request->idea_type);
            return $this->successApiResponse(__('wowCore.displayAllIdea'), $AllIdea);
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
    public function store(IdeaCreateRequest $request)
    {
        //Creating a new Service
        $createdIdea = $this->ideaService->createNewIdea($request->all());
        if ($createdIdea) {
            return $this->successApiResponse(__('wowCore.ideaSuccessCreate'), $createdIdea);
        } else {
            return $this->unprocessableApiResponse(__('wowCore.ideaCreateError'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ideas  $id
     * @return \Illuminate\Http\Response
     */
    public function show($idea_id)
    {
        try {
            $particularIdea = $this->ideaService->getParticularIdea($idea_id);
            return $this->successApiResponse(__('wowCore.displayIdea'), $particularIdea);
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ideas  $Ideas
     * @return \Illuminate\Http\Response
     */
    public function update(IdeaCreateRequest $request, $idea_id)
    {
        try {
            $particularIdea = $this->ideaService->updateParticularIdea($request->all(), $idea_id);
            if ($particularIdea) {
                return $this->successApiResponse(__('wowCore.updatedIdea'));
            } else {
                return $this->errorApiResponse(__('wowCore.internalServerError'));
            }
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }


    /**
     * get ideas with tier type.
     *
     * @return \Illuminate\Http\Response
     */
    public function trackerIdeas()
    {
        try {
            $tierIdeas = $this->ideaService->getTierIdeas();
            if ($tierIdeas) {
                return $this->successApiResponse(__('wowCore.showTrackerIdeas'),$tierIdeas);
            } else {
                return $this->errorApiResponse(__('wowCore.internalServerError'));
            }
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ideas  $Idea
     * @return \Illuminate\Http\Response
     */
    public function destroy($idea_id)
    {
        try {
            // Deleting Idea and its relationship from the database
            DB::beginTransaction();
            $deletedIdea = $this->ideaService->deleteIdea($idea_id);
            RelationalGrid::where('idea_id', '=', $idea_id)->delete();
            WowTracker::where('idea_id', '=', $idea_id)->delete();
            DB::commit();
            if ($deletedIdea) {
                return $this->successApiResponse(__('wowCore.deleteIdeaSuccess'));
            } else {
                return $this->errorApiResponse(__('wowCore.internalServerError'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }
}
