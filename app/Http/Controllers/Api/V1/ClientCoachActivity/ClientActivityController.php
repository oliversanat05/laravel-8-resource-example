<?php

namespace App\Http\Controllers\Api\V1\ClientCoachActivity;

use App\Models\EventTrack;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientCollection;
use App\Http\Requests\clientCoachActivity\ClientActivityRequest;

class ClientActivityController extends Controller
{
    public function trackClientactivity(ClientActivityRequest $request)
    {

        $pageSize = $request->query('pageSize');

        $trackEvent = EventTrack::getClientActivity($request->startDate, $request->endDate, $pageSize);

        return new ClientCollection($trackEvent);
    }
}
