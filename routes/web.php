<?php


use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\MikroTikController;
use Illuminate\Http\Request;


Route::get('/wifi', [ClientController::class, 'index']);
Route::post('/wifi', [ClientController::class, 'store'])->name('wifiaccess');

// Token verification routes
Route::get('/token/verify', [ClientController::class, 'showTokenVerification'])->name('token.verification');
Route::post('/token/verify', [ClientController::class, 'verifyToken'])->name('verify.token');
Route::get('/token/check', [ClientController::class, 'checkToken'])->name('check.token');

Route::get('/finale', [ClientController::class, 'finale'])->name('verification.page');

// MikroTik API routes
Route::get('/mikrotik/test', [MikroTikController::class, 'testConnection']);
Route::get('/mikrotik/addresses', [MikroTikController::class, 'getAddresses']);
Route::get('/mikrotik/profiles', [MikroTikController::class, 'getHotspotProfiles']);
Route::get('/mikrotik/users', [MikroTikController::class, 'getHotspotUsers']);
Route::get('/mikrotik/create-test-users', [MikroTikController::class, 'createTestUsers']);
Route::get('/mikrotik/check-profiles', [MikroTikController::class, 'checkAndCreateProfiles']);
Route::get('/mikrotik/cleanup-users', [MikroTikController::class, 'cleanupDuplicateUsers']);

Route::get('/verification/success', function () {
    return view('verification_success');
})->name('verification_success');

Route::get('/verification/failed', function () {
    return view('verification_failed');
})->name('verification_failed');

/*Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');
*/

Route::get('/email/verify', [ClientController::class, 'verifyEmail'])->name('verify.email');
Route::get('/redirect2', [ClientController::class, 'redirect2'])->name('redirect2');

// Test email route (protected with auth for security)
Route::get('/test-email', [ClientController::class, 'testEmail'])->middleware('auth')->name('test.email');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Auth::routes();
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/clients', [DashboardController::class, 'clients'])->name('clients');
    Route::patch('/clients/{id}/deactivate', [DashboardController::class, 'deactivateUser'])->name('clients.deactivate');
    Route::get('/statistics', [DashboardController::class, 'statistics'])->name('statistics');
    Route::get('/bandwidth-data', [DashboardController::class, 'getBandwidthData'])->name('bandwidth.data');
    Route::get('/clients/export', [DashboardController::class, 'exportClients'])->name('clients.export');
    Route::post('/ban-user', [DashboardController::class, 'banUser']);
    
    // User deletion test routes
    Route::get('/test-delete-expired-users', [DashboardController::class, 'testDeleteExpiredUsers'])->name('test.delete.expired');
    Route::get('/schedule-user-for-deletion/{client_id}', [DashboardController::class, 'manuallyScheduleUserForDeletion'])->name('schedule.deletion');
    
    // Settings routes
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.update');
});