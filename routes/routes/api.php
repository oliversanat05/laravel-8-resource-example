<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\Pdf\PDFController;
use App\Http\Controllers\Api\V1\VMapApiController;
use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\WowLoginController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\QualifierController;
use App\Http\Controllers\Api\V1\VMap\VMapController;
use App\Http\Controllers\Access\PageAccessController;
use App\Http\Controllers\Api\V1\Pages\PageController;
use App\Http\Controllers\Api\V1\VmapFilterController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Avatar\AvatarController;
use App\Http\Controllers\Api\V1\CallMaximizerController;
use App\Http\Controllers\Api\V1\VMap\VMapCopyController;
use App\Http\Controllers\Api\V1\Roles\UserRoleController;
use App\Http\Controllers\Api\V1\Profile\ProfileController;
use App\Http\Controllers\Api\V1\VMap\VMapActionController;
use App\Http\Controllers\Api\V1\VMap\VMapLevelsController;
use App\Http\Controllers\Api\V1\VMap\VMapStatusController;
use App\Http\Controllers\Api\V1\VMapCsv\VMapCsvController;
use App\Http\Controllers\Api\V1\ActivityTitleListController;
use App\Http\Controllers\Api\V1\Delegates\DelegateController;
use App\Http\Controllers\Api\V1\VMap\VMapDashboardController;
use App\Http\Controllers\Api\V1\CoachPath\CoachPathController;
use App\Http\Controllers\Api\V1\CoachPath\SweetSpotController;
use App\Http\Controllers\Api\V1\wowAccess\WowAccessController;
use App\Http\Controllers\Api\V1\CoachPath\ActionItemController;
use App\Http\Controllers\Api\V1\VMap\VMapUpdateOrderController;
use App\Http\Controllers\Api\V1\CoachPath\CoreDisciplineController;
use App\Http\Controllers\Api\V1\Delegates\DelegateStatusController;
use App\Http\Controllers\Api\V1\Settings\GeneralSettingsController;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
use App\Http\Controllers\Api\V1\ClientController\ClientDataController;
use App\Http\Controllers\Api\V1\ClientCoachActivity\CoachActivityController;
use App\Http\Controllers\Api\V1\ClientCoachActivity\ClientActivityController;
use App\Http\Controllers\Api\V1\DataTrackingController\DataTrackingController;
use App\Http\Controllers\Api\V1\CallMaximizer\CallMaximizerComponentController;
use App\Http\Controllers\Api\V1\CallMaximizer\CallMaximizerOverDueComponentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

//All wow service Apis - are linked in wowApi.php

// Route::group(['middleware' => ['cors']], function () {


    Route::post('login', [LoginController::class, 'login']);
    Route::post('wow-login', [WowLoginController::class, 'login']);
    // Secret Key validation
    Route::post('verify-auth-token', [WowLoginController::class, 'keyValidation']);

    //forgot password api
    Route::post('/forgot-password', [ForgotPasswordController::class, 'resetPassword']);
    Route::post('/change-password', [ForgotPasswordController::class, 'updatePassword'])->name('forgot-password');

    //notification route
    Route::get('group-update/{type}/{key}', [DashboardApiController::class, 'getUserNotificationData']);

    Route::group(['middleware' => ['auth:api']], function () {

        //get activity list
        Route::get('activities', [ActivityController::class, 'index']);

        //get activity title list
        Route::apiResource('activity-titles', ActivityTitleListController::class);

        //this route will give the vmap content
        Route::get('vmaps', [VMapApiController::class, 'index']);
        //this route will give the deleted vmaps
        Route::get('vmaps-deleted-items', [VMapApiController::class, 'deletedVMapsList']);
        //this route will rollback the deleted vMap
        Route::put('{type}/{id}/restore', [VMapApiController::class, 'restore']);

        //vmap action routes

        Route::apiResource('vmap', VMapActionController::class);

        //this route will update the status of the vmap levels
        Route::put('vmap-level-status/{id}/{type}', [VMapStatusController::class, 'update']);

        //route update vmap levels
        Route::put('vmap-levels/{levelId}/{type}', [VMapLevelsController::class, 'update']);
        //this route will delete the vMap levels
        Route::delete('vmap-levels/{vmapId}/{level}', [VMapLevelsController::class, 'destroy']);

        //this route will make a copy of the existing vmaps
        Route::post('vmaps/{vMapId}/copy', [VMapCopyController::class, 'store']);

        //this route will add or remove the vmap from the dashboard
        Route::post('vmap-dashboard/{vmapId}', [VMapDashboardController::class, 'store']);

        //this route will give the qualifier list associated with the logged in user
        Route::apiResource('vmap-qualifiers', QualifierController::class);

        //vmap filters routes
        Route::get('vmap-filters', [VmapFilterController::class, 'index']);

        //this route will update the order of the vmap and its related children
        Route::put('vmap-order', [VMapUpdateOrderController::class, 'update']);

        //this route will add or update the tracking data
        Route::apiResource('tracking-data', DataTrackingController::class)->except(['show']);

        //this route will get all the roles of the user
        Route::get('roles', [UserRoleController::class, 'index']);
        //this route will update the role access
        Route::put('roles-update', [UserRoleController::class, 'update']);
        //this route will get the role count
        Route::get('roles-count', [UserRoleController::class, 'getUserCountByRole']);

        //client route
        Route::get('clients', [ClientDataController::class, 'clientData']);

        //page route
        Route::get('pages', [PageController::class, 'getPageList']);

        //callmaximizer routes
        Route::apiResource('callmaximizer', CallMaximizerController::class);

        //this route will update the callmaximizer's activities => reflection, assignment etc
        Route::put('callmaximizer-component/{id}', [CallMaximizerComponentController::class, 'update']);

        //this route will update the next coaching session
        Route::put('callmaximizer-coaching-session/{id}', [CallMaximizerController::class, 'updateNextCoachingSession']);

        //this route will update the overdue activity
        Route::put('callmaximizer-overdue-activity/{id}', [CallMaximizerOverDueComponentController::class, 'overDueActivityUpdate']);
        Route::get('callmaximizer-overdue-activity/list', [CallMaximizerOverDueComponentController::class, 'overDueActivityList']);

        //user management routes
        Route::apiResource('users', UserController::class);
        // get user list
        Route::post('users-count', [UserController::class, 'usersCount']);
        // Route::get('user/details/{id}', [UserController::class, 'userDetails']);

        /* Fetch user API route */
        Route::get('user-data/{id}', [UserController::class, 'getUserData']);

        /* Fetch user API route */
        Route::get('user-count-by-role', [UserController::class, 'countRoleUser']);

        /* Fetch timezones */
        Route::get('timezones', [UserController::class, 'timezones']);

        /* Fetch assigned coach */
        Route::get('assigned-coach/{id}', [UserController::class, 'assignedCoaches']);

        //this route will get the coach & client activity
        Route::post('coach-activities', [CoachActivityController::class, 'trackCoachActivity']);

        Route::post('client-activities', [ClientActivityController::class, 'trackClientactivity']);

        //this route will get the user's profile information
        Route::get('profile', [ProfileController::class, 'index']);

        //for updating the profile of the logged in user
        Route::post('profile', [ProfileController::class, 'store']);

        //manage delegate routes
        Route::apiResource('delegate', DelegateController::class);
        //this route will delete the delegate status to active or inactive or vice-versa
        Route::put('delegate-status/{id}', [DelegateStatusController::class, 'changeDelegateStatus']);

        Route::post('notification', [NotificationController::class, 'getNotification']);

        //General settings route
        Route::put('settings', [GeneralSettingsController::class, 'update']);

        //Dashboard profile routes
        Route::apiResource('dashboard-profile', DashboardController::class);
        Route::delete('dashboard-profile-vmap/{id}', [DashboardController::class, 'deleteVmap']);

        Route::get('callmaximizer-list', [CallMaximizerController::class, 'getCallMaximizerData']);

        //getting all the accessed pages of the user
        Route::get('page-access', [PageAccessController::class, 'index']);

        //for updating the profile image of the user
        Route::post('profile-image', [ProfileController::class, 'uploadProfileImage']);


        //getting all the accessed pages of the user
        Route::get('communication/{userId}', [DelegateController::class, 'communication']);
        //for getting the values for the filters
        Route::get('vmap-values', [VMapController::class, 'values']);

        //get all the kpis for the values
        Route::get('value-kpis', [VMapController::class, 'kpis']);

        //get all the level3 related to level2
        Route::get('kpi-strategies', [VMapController::class, 'strategies']);

        //get all the level 4 related to level 3
        Route::get('strategy-projects', [VMapController::class, 'projects']);

        //get all the level 5 related to level 4
        Route::get('project-critical-activities', [VMapController::class, 'criticalActivities']);

        //get auto-suggestion for assigning delegates
        Route::get('get-suggested-delegate-users', [DelegateController::class, 'getAutoSuggestedDelegates']);

        //assign the delegate to the user
        Route::post('assign-delegate', [DelegateController::class, 'assignDelegates']);

        //revert the tracking data deletion
        Route::post('revert-tracking-data-deletion', [DataTrackingController::class, 'deleteSystemGeneratedTrackingData']);

        //get the deleted tracking data
        Route::get('get-deleted-tracking-data', [DataTrackingController::class, 'getDeletedTrackingDataList']);

        //save the system generated tracking data
        Route::post('system-generated-tracking-data', [DataTrackingController::class, 'insertAutoGeneratedTrackingData']);

        //delete callmaximizer assignment component
        Route::post('delete-callmaximizer-assignment', [CallMaximizerController::class, 'deleteCallMaximizerAssignment']);

        //get dashboard filter data component
        Route::post('get-dashboard-filter-data', [DashboardApiController::class, 'getDashboardDataApi']);

        //for getting the values for the filters
        Route::post('values', [VMapController::class, 'getValues']);

        //for getting the kpis for the filters
        Route::post('kpis', [VMapController::class, 'getKpis']);

        //for getting the kpis for the filters
        Route::get('activity-details/{level}/{id}', [VMapController::class, 'getActivityDetails']);
        //for getting the logged in user's client list
        Route::get('client-list', [ClientDataController::class, 'clientData']);

        //user simulation
        Route::post('simulate', [ClientDataController::class, 'simulate']);

        //for getting the users that have wow access
        Route::get('wow-access', [WowAccessController::class, 'index']);

        //update the wow access for the users
        Route::put('wow-access', [WowAccessController::class, 'update']);

        //for exporting the csv
        Route::get('export-csv', [VMapCsvController::class, 'exportVmapCsv']);

        //for exporting the vmap pdf
        Route::get('export-pdf', [PDFController::class, 'exportPDF']);

        //for creating the vmap levels
        Route::post('create-vmap-levels/{parentId}/{levelType}', [VMapLevelsController::class, 'store']);

        //for creating the vmap level 1
        Route::post('vmap/{vmapId}/values', [VMapLevelsController::class, 'createValueLevel']);
        //for updating the coach path
        Route::get('coaching-readiness', [CoachPathController::class, 'getCoachPathData']);

        //resource routes for coach path
        Route::apiResource('coach-path', CoachPathController::class);

        //save the coaching readiness question data
        Route::post('save-coaching-readiness-questions/{survey_date}', [CoachPathController::class, 'saveCoachingReadinessData']);

        //sweet spot controller classes
        Route::apiResource('sweet-spot-analysis', SweetSpotController::class);

        //save sweet spot analysis data
        Route::post('save-sweet-spot-analysis-questions/{survey_date}', [SweetSpotController::class, 'saveSweetSpotAnalysisData']);

        //for saving the avatar details
        Route::post('save-avatar', [AvatarController::class, 'saveAvatarData']);

        //for fetching the avatar details for the logged in user
        Route::get('avatar', [AvatarController::class, 'index']);

        //core discipline routes
        Route::apiResource('core-discipline', CoreDisciplineController::class);

        //save core discipline data
        Route::post('save-core-discipline-questions/{surveyDate}', [CoreDisciplineController::class, 'saveCoreDisciplineData']);

        //action item routes
        Route::get('action-item', [ActionItemController::class, 'index']);


        Route::post('action-item', [ActionItemController::class, 'store']);

        //only in development mode
        Route::get('all-vmaps', [VMapApiController::class, 'getVmapData']);
        //for importing the csv
        Route::post('import-csv/{vmapId}', [VMapCsvController::class, 'importVmapCsv']);

        //route for updating the level's description and due date from the dashboard
        Route::post('dashboard-quick-update', [DashboardApiController::class, 'dashboardQuickUpdate']);

        /* this route will be used for call maximizer */
        Route::post('getSpecificActivity', [DashboardApiController::class, 'getSpecificActivity']);

        //to get the user's global avatar health
        Route::get('user-global-avatar-health', [DashboardApiController::class, 'avatarGlobalHealth']);

        require __DIR__ . '/wowApi.php';
    });
// });
