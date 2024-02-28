<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoosterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VerlofaanvraagController;
use App\Http\Controllers\NotificationController;

// Route voor de inlogpagina
Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');

// Routes voor roosterbeheer
Route::middleware(['auth'])->group(function () {
    Route::get('/roosters', [RoosterController::class, 'index'])->name('roosters.index');
    Route::put('/roosters/{id}', [RoosterController::class, 'update'])->name('roosters.update');
    Route::delete('/roosters/{id}', [RoosterController::class, 'destroy'])->name('roosters.destroy');
    Route::put('/roosters/{id}/updateDate', [RoosterController::class, 'updateDate'])->name('roosters.updateDate');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

    Route::get('/verlofaanvragen', [VerlofaanvraagController::class, 'index'])->name('verlofaanvragen.index');
    Route::post('/verlofaanvragen', [VerlofaanvraagController::class, 'store'])->name('verlofaanvragen.store');
    Route::post('/verlofaanvragen/approve/{id}', [VerlofaanvraagController::class, 'approve'])->name('verlofaanvragen.approve');
    Route::delete('/verlofaanvragen/{id}/delete', [VerlofaanvraagController::class, 'destroy'])->name('verlofaanvragen.delete');
    Route::post('/verlofaanvragen/reject/{id}', [VerlofaanvraagController::class, 'reject'])->name('verlofaanvragen.reject');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Routes alleen toegankelijk voor admins
Route::middleware(['auth', 'admin'])->group(function () {
    Route::delete('/remove-from-rooster/{roosterId}', [UserController::class, 'removeFromRooster'])->name('removeFromRooster');
    Route::get('/editTimes/{userId}', [UserController::class, 'editTimes'])->name('editTimes');
    Route::get('/beheer', [UserController::class, 'index'])->name('admin.index');
    Route::get('/add-rooster', [RoosterController::class, 'addRooster'])->name('addRooster');
    Route::get('/roosters/create', [RoosterController::class, 'createRooster'])->name('createRooster');
    Route::post('/roosters/store', [RoosterController::class, 'storeRooster'])->name('storeRooster');
    Route::post('/store-rooster/{date}', [RoosterController::class, 'storeRooster'])->name('storeRooster');
    Route::post('/roosters/{id}/process-swap-request/{selectedUser}', [RoosterController::class, 'processSwapRequest'])->name('roosters.processSwapRequest');
    Route::get('/roosters/{id}/edit', [RoosterController::class, 'edit'])->name('roosters.edit');
    Route::post('/roosters/approve/{id}', [RoosterController::class, 'updateApprovalStatus'])->name('roosters.approve');
    Route::post('/roosters/reject/{id}', [RoosterController::class, 'updateApprovalStatus'])->name('roosters.reject');
    Route::get('/ruilen/{id}', [RoosterController::class, 'showSwapRequest'])->name('requestSwap');
    Route::post('/process-swap-request', [RoosterController::class, 'processSwapRequest'])->name('processSwapRequest');
    Route::get('/get-user-dates/{userId}', [RoosterController::class, 'getUserDates']);
    Route::post('/roosters/{id}/process-swap-request', [RoosterController::class, 'processSwapRequest'])->name('roosters.processSwapRequest');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}/roles', [UserController::class, 'roles'])->name('users.roles');
    Route::post('/users/{id}/assignRole', [UserController::class, 'assignRole'])->name('users.assignRole');
    Route::delete('/users/{id}/removeRole', [UserController::class, 'removeRole'])->name('users.removeRole');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::get('/users/{id}', [UserController::class, 'showProfile'])->name('users.showProfile');
    Route::get('/tasks', [UserController::class, 'showTasks'])->name('tasks.index');
    Route::post('/tasks', [UserController::class, 'storeTask'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [UserController::class, 'editTask'])->name('tasks.edit');
    Route::put('/tasks/{task}', [UserController::class, 'updateTask'])->name('tasks.update');
    Route::delete('/tasks/{task}', [UserController::class, 'destroyTask'])->name('tasks.destroy');
});

Route::middleware(['auth', 'user.is.authorized'])->group(function () {
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    // Andere routes die deze check vereisen
});

// Algemene gebruikersbeheerroutes
// Route::get('/users', [UserController::class, 'index'])->name('users.index');
// Route::get('/assign-admin-role/{userId}', [UserController::class, 'showAssignAdminRoleForm'])->name('assign.admin.role.form');
// Route::post('/assign-admin-role/{id}', [UserController::class, 'assignAdminRole'])->name('assign-admin-role');

// Dashboardroute voor ingelogde gebruikers
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Profielbeheerroutes
Route::middleware('auth')->group(function () {
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authenticatieroutes
require __DIR__ . '/auth.php';
