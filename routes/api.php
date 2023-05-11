<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubAdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// USER

Route::post('login', [AuthController::class, 'login']);
Route::group([

    'middleware' => 'api',
    // 'prefix' => 'auth'

], function ($router) {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

//ADMIN
Route::post('admin/login', [AdminController::class, 'login']);

Route::group([
    'middleware' => 'admin',
    'prefix' => 'admin'
], function ($router) {
    Route::post('logout', [AdminController::class, 'logout']);
    Route::post('refresh',  [AdminController::class, 'refresh']);
    Route::post('me',  [AdminController::class, 'me']);
});

//SubAdmin -> Extra Login Route
Route::post('sub/login', [SubAdminController::class, 'login']);

Route::group([
    'middleware' => 'subAdmin',
    'prefix' => 'sub'
], function ($router) {
    Route::post('logout', [SubAdminController::class, 'logout']);
    Route::post('refresh',  [SubAdminController::class, 'refresh']);
    Route::post('me',  [SubAdminController::class, 'me']);
});
