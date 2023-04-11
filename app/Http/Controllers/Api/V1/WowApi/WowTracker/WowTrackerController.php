<?php

namespace App\Http\Controllers\Api\V1\WowApi\WowTracker;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\wowRequests\ValidateWowDataRequest;
use App\Services\wowService\WowTrackerService\WowTrackerService;
use Illuminate\Support\Facades\DB;

class WowTrackerController extends Controller
{
    use ApiResponse;

    public function __construct() {
		$this->wowTracker = new WowTrackerService();
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $headings = $this->wowTracker->trackerHeadings();
            if (!$headings) {
                return $this->unprocessableApiResponse(__('wowCore.exists'));
            }
            return $this->successApiResponse(__('wowCore.showTrackerData'),$headings);
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
   public function store(Request $request)
    {
        //currently not in use
        // try {
        //     $trackerStatus = $this->wowTracker->storeOrUpdateWowTrackerData($request->all());
        //     if (!$trackerStatus) {
        //         return $this->unprocessableApiResponse(__('wowCore.statusUpdatedError'));
        //     }
        //     return $this->successApiResponse(__('wowCore.wowTrackerSaved'));

        // } catch (\Exception $e) {
        //     return $this->errorApiResponse($e);
        // }
    } 

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $trackerData = $this->wowTracker->wowTrackerData($id);
            if (!$trackerData) {
                return $this->unprocessableApiResponse(__('wowCore.exists'));
            }
            return $this->successApiResponse(__('wowCore.wowTrackerData'),$trackerData);
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
    public function update(ValidateWowDataRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $trackerStatus = $this->wowTracker->storeOrUpdateWowTrackerData($request->all(),$id);
            DB::commit();
            if (!$trackerStatus) {
                return $this->unprocessableApiResponse(__('wowCore.statusUpdatedError'));
            }
            return $this->successApiResponse(__('wowCore.wowTrackerSaved'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorApiResponse($e);
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
        //
    }
}
