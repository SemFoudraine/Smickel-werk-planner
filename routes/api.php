<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoosterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;

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

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/roosterdata', [RoosterController::class, 'getRoosterData']);
    Route::get('/userdata', [UserController::class, 'getUser']);
    Route::get('/user/hours', [DashboardController::class, 'getUserHours']);
    Route::get('/tasks', [TaskController::class, 'getTasks']);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::delete('/roosters/{id}', [RoosterController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
