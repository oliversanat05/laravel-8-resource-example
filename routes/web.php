<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SSOClientController;
use App\Http\Controllers\Api\V1\Avatar\AvatarController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('login', [SSOClientController::class, 'login'])->name('login');
Route::get('callback', [SSOClientController::class, 'callback']);

//open route for creating the avatar
Route::get('get-avatar', [AvatarController::class, 'getAvatar']);


Route::get('/', function () {
    return view('welcome');
});
