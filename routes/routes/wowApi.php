<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\WowApi\Tiers\TierController;
use App\Http\Controllers\Api\V1\WowApi\Ideas\IdeasController;
use App\Http\Controllers\Api\V1\WowApi\Client\ClientController;
use App\Http\Controllers\Api\V1\WowApi\Profile\ProfileController;
use App\Http\Controllers\Api\V1\WowApi\Tiers\AdminTierController;
use App\Http\Controllers\Api\V1\WowApi\WowTracker\WowTrackerController;
use App\Http\Controllers\Api\V1\WowApi\MetricAreas\MetricAreaController;
use App\Http\Controllers\Api\V1\WowApi\ClientScore\ClientScoreController;
use App\Http\Controllers\Api\V1\WowApi\MetricAreas\AdminMetricController;
use App\Http\Controllers\Api\V1\WowApi\MetircHeading\MetricHeadingController;
use App\Http\Controllers\Api\V1\WowApi\RelationalGrid\RelationalGridController;
use App\Http\Controllers\Api\V1\WowApi\ClientMetricData\ClientMetricDataController;
use App\Http\Controllers\Api\V1\WowApi\StatementListing\StatementListingController;
use App\Http\Controllers\Api\V1\WowApi\GapAnalysisHeading\GapAnalysisHeadingController;
use App\Http\Controllers\Api\V1\WowApi\GapAnalysisScoring\GapAnalysisScoringController;
use App\Http\Controllers\Api\V1\WowApi\ResponsiblePerson\ResponsiblePersonController;

/*
|--------------------------------------------------------------------------
| Wow Services API Routes
|--------------------------------------------------------------------------
|
| Here is where all the Request and Response for wow feature will
| be executed and handled.
|
 */
    // Get logged-in user details
    Route::get('user', [ProfileController::class, 'userDetails']);
    //User Metric Area resource controller
    Route::resource('metric-areas', MetricAreaController::class);
    // Tier Update if tiers doesn't present for user then create
    Route::put('tiers/{userId}', [TierController::class, 'update']);
    // Activating and deactivating Tiers
    Route::put('tiers/change-status/{tierId}', [TierController::class, 'changeTierStatus']);
    // Showing user Tiers
    Route::get('tiers/{userId}', [TierController::class, 'show']);
    // Showing user Tiers
    Route::get('enabledTiers/{userId}', [TierController::class, 'showEnabled']);
    //Statement Listing
    Route::apiResource('listing', StatementListingController::class);
    //get Last Listing Id
    Route::get('last-listing-id', [StatementListingController::class, 'getLastListingId']);
    //Statement Listing
    Route::post('listing/copy', [StatementListingController::class, 'copy']);
    //Client Metric Data
    Route::resource('client-metric-data', ClientMetricDataController::class);
    //Client Scoring
    Route::resource('client-scores', ClientScoreController::class);
    //Metric Heading
    Route::resource('metric-headings', MetricHeadingController::class);
    //Statement listing API
    Route::resource('listing', StatementListingController::class);
    //Responsible Person
    Route::resource('responsible-persons', ResponsiblePersonController::class);
    //Ideas API
    Route::resource('ideas', IdeasController::class);
    Route::get('wow-tracker-ideas', [IdeasController::class, 'trackerIdeas']);
    //Relational Grids APIs`
    Route::resource('relational-grids', RelationalGridController::class);
    //mass Delete Ideas
    Route::post('services/massDelete', [RelationalGridController::class, 'massDelete']);
    //Client resource controller
    Route::resource('clients', ClientController::class);
    //Gap Analysis Heading
    Route::resource('gap-analysis/headings', GapAnalysisHeadingController::class);
    //Client Gap Analysis Scoring
    Route::resource('gap-analysis-scoring', GapAnalysisScoringController::class);
    // Wow Tracker
    Route::resource('wow-tracker', WowTrackerController::class);

    Route::prefix('admin')->group(function () {
        //Admin Metric Area resource controller
        Route::apiResource('admin-metric-areas', AdminMetricController::class);
        // Create default metric
        Route::post('tiers', [AdminTierController::class, 'store']);
        // update default metric
        Route::put('tiers', [AdminTierController::class, 'update']);
        // get default metric
        Route::get('tiers', [AdminTierController::class, 'index']);
    });
