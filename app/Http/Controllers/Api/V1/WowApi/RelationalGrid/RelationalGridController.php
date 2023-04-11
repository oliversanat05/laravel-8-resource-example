<?php

namespace App\Http\Controllers\Api\V1\WowApi\RelationalGrid;

use DB;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\wowModels\RelationalGrid;
use App\Http\Requests\wowRequests\GridShowRequest;
use App\Http\Requests\wowRequests\RelationalGridCreateRequest;
use App\Services\wowService\RelationalGridService\RelationalGridService;

class RelationalGridController extends Controller
{
    use ApiResponse;
    /**
     * Creating an instance of IdeaService
     * at begning
     */
    public function __construct()
    {
        $this->gridServices = new RelationalGridService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GridShowRequest $request)
    {
        try {
            $AllGrid= $this->gridServices->getAllGridService($request->idea_type);
            if(count($AllGrid))  return $this->successApiResponse(__('wowCore.displayAllGrid'), $AllGrid);
            else return $this->unprocessableApiResponse(__('wowCore.noGridsFound'));
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
    public function store(RelationalGridCreateRequest $request)
    {
        try {
            $data = $request->all();
            //Creating a new Metric
            $createdGrid = $this->gridServices->createOrUpdateGrid($data);
            if ($createdGrid) return $this->successApiResponse(__('wowCore.gridSuccessCreate'));
            else return $this->unprocessableApiResponse(__('wowCore.gridCreateFailed'));

        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $relationalGrid
     * @return \Illuminate\Http\Response
     */
    public function show($relationalGridId)
    {
        try {
            $particularCLientGrid = $this->gridServices->showParticularGrid($relationalGridId);
            if(count($particularCLientGrid)) return $this->successApiResponse(__('wowCore.relationalGridShow'), $particularCLientGrid);
            return $this->unprocessableApiResponse(__('wowCore.noRelationalGrid'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
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
    public function update(Request $request,RelationalGrid $relationalGrid)
    {
        try {
            $updatedGrid = $this->gridServices->updateGrid($request->all(),$relationalGrid);
            if ($updatedGrid) return $this->successApiResponse(__('wowCore.gridSuccessUpdate'), $updatedGrid);
            else return $this->unprocessableApiResponse(__('wowCore.gridUpdateFailed'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $relationalGrid
     * @return \Illuminate\Http\Response
     */
    public function destroy($relationalGridId)
    {
        try {
            // Deleting Idea from a database
            $deletedGrid = $this->gridServices->deleteGridService($relationalGridId);
            if ($deletedGrid) return $this->successApiResponse(__('wowCore.deleteGridSuccess'));
            else return $this->errorApiResponse(__('wowCore.internalServerError'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $relationalGrid
     * @return \Illuminate\Http\Response
     */
    public function massDelete(Request $request)
    {
        try {
            // Deleting Idea from a database
            $deletedGrid = $this->gridServices->massDeleteGridService($request->data);
            if ($deletedGrid) return $this->successApiResponse(__('wowCore.deleteGridSuccess'));
            else return $this->errorApiResponse(__('wowCore.internalServerError'));
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

}
