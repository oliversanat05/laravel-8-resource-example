<?php

namespace App\Http\Controllers\Api\V1;

use Auth;
use App\Traits\ApiResponse;
use App\Services\VMapSystem;
use Illuminate\Http\Request;
use App\Services\FilterSystem;
use App\Models\Succession\VMap;
use App\Services\VMapFilterService;
use App\Http\Controllers\Controller;
use App\Http\Resources\VMapResource;
use App\Services\VMapHelperServices\VMapHelpers;


class VmapFilterController extends Controller
{

    use ApiResponse;
    private $vMap;
    private $vMapHelper;
    /**
     * constructor called
     */
    public function __construct()
    {
        $this->vMapFilter = new VMapFilterService();
        $this->vMap = new VMapSystem();
        $this->filter = new FilterSystem();
        $this->vMapHelper = new VMapHelpers();
    }
    /**
     * method used to filter a list of vmaps
     */
    public function index(Request $request){
        $params = $request->all();
        try {
            if($params){
                $vMapContents = $this->vMapFilter->getFilters($params)->get();
            }else{
                $vMapContents = $this->vMapFilter->getFilterVMap($params['vmap'])->with('values')->get();
            }
            $data = VMapResource::collection($vMapContents);

            return $this->successApiResponse(__('core.vmapFilterFetched'), $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
