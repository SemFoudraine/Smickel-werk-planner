<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoosterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\VerlofaanvraagController;

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
Route::post('refresh', [AuthController::class, 'refresh']);


Route::middleware('auth:api')->group(function () {
    Route::get('/roosterdata', [RoosterController::class, 'getRoosterData']);
    Route::get('/userdata', [UserController::class, 'getUser']);
    Route::get('/user/hours', [DashboardController::class, 'getUserHours']);
    Route::get('/tasks', [TaskController::class, 'getTasks']);
    Route::put('/roosters/{id}', [RoosterController::class, 'updateRooster']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/roosters', [RoosterController::class, 'storeRooster']);

    Route::get('/verlofaanvragen', [VerlofaanvraagController::class, 'index']);
    Route::post('/verlofaanvragen', [VerlofaanvraagController::class, 'store']);
    Route::put('/verlofaanvragen/{id}', [VerlofaanvraagController::class, 'update']);
    Route::delete('/verlofaanvragen/{id}', [VerlofaanvraagController::class, 'destroy']);
    Route::post('/verlofaanvragen/{id}/approve', [VerlofaanvraagController::class, 'approve']);
    Route::post('/verlofaanvragen/{id}/reject', [VerlofaanvraagController::class, 'reject']);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::delete('/remove-from-rooster/{roosterId}', [UserController::class, 'removeFromRooster'])->name('removeFromRooster');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
