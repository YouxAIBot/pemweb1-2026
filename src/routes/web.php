<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\LearningDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/* NOTE: Do Not Remove
/ Livewire asset handling if using sub folder in domain
*/

Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});
/*
/ END
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request.frontend');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email.frontend');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout.get');

Route::middleware('auth')->group(function () {
    Route::get('/welcome', [LearningDashboardController::class, 'welcome'])->name('learning.welcome');
    Route::get('/onboarding', [LearningDashboardController::class, 'onboarding'])->name('learning.onboarding');
    Route::post('/onboarding', [LearningDashboardController::class, 'storeOnboarding'])->name('learning.onboarding.store');
    Route::get('/dashboard', [LearningDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/parts/{part}', [LearningDashboardController::class, 'showPart'])->name('learning.parts.show');
    Route::get('/dashboard/parts/{part}/levels/{level}', [LearningDashboardController::class, 'showLevel'])->name('learning.levels.show');
});
