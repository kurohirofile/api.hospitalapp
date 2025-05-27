<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => 'api',    'prefix' => 'auth'], function ($router) {
    Route::post('register',  [AuthController::class, 'register']);
    Route::post('login',  [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me',  [AuthController::class, 'me']);

});
Route::group(['middleware' => ['jwt.verify']], function ()
{
    Route::get('patient',  [PatientController::class, 'index']);
    Route::post('patient',  [PatientController::class, 'store']);
    Route::put('patient/{id}',  [PatientController::class, 'update']);
    Route::get('patient/{id}',  [PatientController::class, 'show']);
    Route::delete('patient/{id}',  [PatientController::class, 'destroy']);


    Route::post('fileupload',  [PatientController::class, 'fileupload']);
    Route::get('fileupload',  [PatientController::class, 'filelist']);
    Route::delete('fileupload',  [PatientController::class, 'delete']);
});