<?php

namespace App\Http\Controllers\Api\V1\WowApi\ResponsiblePerson;

use App\Http\Controllers\Controller;
use App\Services\wowService\ResponsiblePersonService\ResponsiblePersonService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Requests\wowRequests\ResponsiblePersonRequest;
use App\Models\wowModels\RelationalGrid;

class ResponsiblePersonController extends Controller
{
    use ApiResponse;

     /**
     * Creating an instance of ResponsiblePersonMetric
     *
     */
    public function __construct()
    {
        $this->responsiblePerson = new ResponsiblePersonService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $responsiblePersons = $this->responsiblePerson->showPersons();
            if (!$responsiblePersons) {
                return $this->unprocessableApiResponse(__('wowCore.noResponsiblePersons'));
            }
            return $this->successApiResponse(__('wowCore.responsiblePersons'),$responsiblePersons);
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
        try {
            $responsiblePersons = $this->responsiblePerson->showPersons();
            if (!$responsiblePersons) {
                return $this->unprocessableApiResponse(__('wowCore.noResponsiblePersons'));
            }
            return $this->successApiResponse(__('wowCore.responsiblePersons'),$responsiblePersons);
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
    public function store(ResponsiblePersonRequest $request)
    {
        try {
            $newPerson = $this->responsiblePerson->createPersons($request->all());
            if (!$newPerson) {
                return $this->unprocessableApiResponse(__('wowCore.statusUpdatedError'));
            }
            return $this->successApiResponse(__('wowCore.responsiblePersonCreate'),$newPerson);
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(ResponsiblePersonRequest $request, $id)
    {
        try {
            $updatePerson = $this->responsiblePerson->updatePerson($request->all(),$id);
            if (!$updatePerson) {
                return $this->unprocessableApiResponse(__('wowCore.responsiblepersonExists'));
            }
            return $this->successApiResponse(__('wowCore.updateResponsiblePerson'));
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
    public function destroy($id)
    {
        try {
            //checking whether it is used in any client metric data
            $isUsed=RelationalGrid::where('responsible_person_id',$id)->where('deleted_at',NULL)->exists();
            if(!$isUsed){
                $deletedperson = $this->responsiblePerson->deletePerson($id);
                if (!$deletedperson) {
                    return $this->unprocessableApiResponse(__('wowCore.noResponsiblePersons'));
                }
                return $this->successApiResponse(__('wowCore.responsiblePersonDeleted'));
            }else{
                return $this->unprocessableApiResponse(__('wowCore.responsiblePersonUsed'));
            }
        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }
}
