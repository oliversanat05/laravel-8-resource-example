<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Traits\ApiResponse;
use App\Models\ENCQualifier;
use App\Services\VMapSystem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\VMapHelperServices\VMapHelpers;
use App\Services\QualifierService\QualifierService;
use App\Http\Requests\qualifier\QualifierStoreRequest;
use App\Http\Requests\qualifier\QualifierUpdateRequest;
use Auth;

class QualifierController extends Controller
{
    use ApiResponse;

    private $vMap;
    private $vMapHelper;
    /**
     * contructor called
     */
    public function __construct()
    {
        $this->vMap = new VMapSystem();
        $this->vMapHelper = new VMapHelpers();
        $this->qualifier = new QualifierService();
    }

    /**
     * get all the qualifier list based on the logged in user
     * @param NA
     * @return qualifier JSON
     *
     */
    public function index(Request $request)
    {
        try{
            $data = $this->vMap->getUserQualifier($request->pageSize);
            return $this->successApiResponse(__('core.qualifierList'), $data);
        }catch(\Exception $ex){
            dd($ex);
            return $this->errorApiResponse(__('core.internalServerError'));
        }
    }

    /**
     * this function will create the qualifier
     *
     * @param QualifierStoreRequest $request
     * @return resource
     */
    public function store(QualifierStoreRequest $request)
    {
        return $qualifierSaved = $this->qualifier->createQualifier($request->userId, $request->qualifierName);
    }

    /**
     * this function will update the qualifier details
     *
     * @param QualifierUpdateRequest $request
     * @param $id
     * @return response
     */
    public function update(QualifierUpdateRequest $request, $id)
    {
        return $this->qualifier->updateQualifier($id, $request->name, $request->status);
    }

    /**
     * this function will show the single qualifier detail
     *
     * @param $id
     * @return void
     */
    public function show($id)
    {
        $qualifier = User::where('user_id', $id)->first();

        $encQualifier = ENCQualifier::where('userName', $qualifier->user_id)->where('parentId', Auth::user()->user_id)->with('user')->first();

        $data['data'] = $encQualifier;

        return $this->successApiResponse(__('core.qualifierFetched'), $data);
    }

    /**
     * this function will delete the qualifier details
     *
     * @param $id
     * @return response
     */
    public function destroy($id)
    {
        return $this->qualifier->deleteQualifier($id);
    }
}
