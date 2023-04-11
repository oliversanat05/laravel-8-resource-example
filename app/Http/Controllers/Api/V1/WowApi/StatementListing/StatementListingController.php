<?php

namespace App\Http\Controllers\Api\V1\WowApi\StatementListing;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\wowRequests\StatementListingCopyRequest;
use App\Http\Requests\wowRequests\StatementListingStoreRequest;
use App\Services\wowService\StatementListingService\StatementListingService;

class StatementListingController extends Controller
{
    use ApiResponse;

    /**
     * Creating an instance of StatementListingService
     * at begning
     */
    public function __construct()
    {
        $this->StatementListing = new StatementListingService();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $UserStatementListings = $this->StatementListing->getAllUserStatementListing();
            return $this->successApiResponse(__('wowCore.statementListingShowAll'), $UserStatementListings);
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
    public function store(StatementListingStoreRequest $request)
    {
        try {
            $data = $request->all();
            //Creating a new StatementListing
            $createdStatementListing = $this->StatementListing->createNewStatementListing($data);
            if ($createdStatementListing) {
                return $this->successApiResponse(__('wowCore.statementListingSuccessCreate'), $createdStatementListing);
            } else {
                return $this->unprocessableApiResponse(__('wowCore.statementListingCreateError'));
            }
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
        try {
            $statementAsPerId = $this->StatementListing->getSpecificStatementListingMetricAreaType($id);
            //format response before sending data
            if ($statementAsPerId) {
                return $this->successApiResponse(__('wowCore.statementAsPerId'), $statementAsPerId);
            } else {
                return $this->unprocessableApiResponse(__('wowCore.emptyResponse'));
            }

        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }


    /**
     * Get Last listing Id inserted by the user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getLastListingId()
    {
        try {
            $lastStatement = $this->StatementListing->getLastListingId();
            //format response before sending data
            if ($lastStatement) {
                return $this->successApiResponse(__('wowCore.lastListingId'), $lastStatement);
            } else {
                return $this->unprocessableApiResponse(__('wowCore.noListingYet'));
            }

        } catch (\Exception $e) {
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Copy Data from old listing;
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function copy(StatementListingCopyRequest $request)
    {
        try {
            $data = $request->all();
            $SuccessfullyCreated = $this->StatementListing->replicateData($data);
            if ($SuccessfullyCreated) {
                return $this->successApiResponse(__('wowCore.copyListing'), $SuccessfullyCreated);
            } else {
                return $this->unprocessableApiResponse(__('wowCore.listingError'));
            }
        } catch (\Exception $e) {
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
        try {
            DB::beginTransaction();
            $statementStatus = $this->StatementListing->deleteSpecificStatementListing($id);
            DB::commit();
            if ($statementStatus) {       
                return $this->successApiResponse(__('wowCore.deletedstatement'), $statementStatus);
            } else {
                return $this->unprocessableApiResponse(__('wowCore.emptyResponse'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorApiResponse(__('wowCore.internalServerError'));
        }
    }
}
