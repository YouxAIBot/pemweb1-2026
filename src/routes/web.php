<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\LearningDashboardController;
use App\Http\Controllers\Frontend\DuelController;
use App\Http\Controllers\Frontend\QuizRoomController;
use App\Http\Controllers\Frontend\UserProfileController;
use App\Http\Controllers\Frontend\PremiumController;
use App\Http\Controllers\Frontend\AdImpressionController;
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
Route::view('/owner', 'frontend.owner')->name('owner');

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
    Route::post('/dashboard/switch-language', [LearningDashboardController::class, 'switchLanguage'])->name('learning.language.switch');
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('learning.profile.edit');
    Route::get('/setting', [UserProfileController::class, 'edit'])->name('learning.settings');
    Route::post('/profile', [UserProfileController::class, 'update'])->name('learning.profile.update');
    Route::post('/setting/preferences', [UserProfileController::class, 'updatePreferences'])->name('learning.settings.preferences.update');
    Route::post('/setting/reset-progress', [UserProfileController::class, 'resetProgress'])->name('learning.settings.progress.reset');
    Route::get('/premium', [PremiumController::class, 'index'])->name('learning.premium');
    Route::get('/toko', [PremiumController::class, 'index'])->name('learning.store');
    Route::post('/premium/payments', [PremiumController::class, 'store'])->name('learning.premium.payments.store');
    Route::post('/premium/payments/midtrans', [PremiumController::class, 'midtrans'])->name('learning.premium.payments.midtrans');
    Route::post('/api/ads/impressions', [AdImpressionController::class, 'store'])->name('api.ads.impressions.store');
    Route::get('/huruf', [LearningDashboardController::class, 'letters'])->name('learning.letters');
    Route::get('/games', fn () => redirect()->route('learning.games'));
    Route::get('/turnamen', [LearningDashboardController::class, 'games'])->name('learning.games');
    Route::get('/tournament', fn () => redirect()->route('learning.tournament'));
    Route::get('/turnamen/cepat', [LearningDashboardController::class, 'tournament'])->name('learning.tournament');
    Route::get('/turnamen/video-question', fn () => redirect()->route('learning.games'))->name('learning.video-question');
    Route::post('/turnamen/video-question', fn () => redirect()->route('learning.games'))->name('learning.video-question.submit');
    Route::get('/turnamen/duel', [DuelController::class, 'lobby'])->name('learning.duel.lobby');
    Route::get('/turnamen/duel/history', [DuelController::class, 'history'])->name('learning.duel.history');
    Route::post('/turnamen/duel/find-match', [DuelController::class, 'findMatch'])->name('learning.duel.find');
    Route::get('/turnamen/duel/queue-status', [DuelController::class, 'queueStatus'])->name('learning.duel.queue.status');
    Route::post('/turnamen/duel/cancel-queue', [DuelController::class, 'cancelQueue'])->name('learning.duel.queue.cancel');
    Route::get('/api/turnamen/duel/{duelSession}/state', [DuelController::class, 'state'])->name('api.duel.state');
    Route::post('/api/turnamen/duel/{duelSession}/answer', [DuelController::class, 'answer'])->name('api.duel.answer');
    Route::post('/api/turnamen/duel/{duelSession}/finish', [DuelController::class, 'finish'])->name('api.duel.finish');
    Route::get('/turnamen/duel/{duelSession}', [DuelController::class, 'room'])->name('learning.duel.room');
    Route::get('/turnamen/quiz', [QuizRoomController::class, 'index'])->name('learning.quiz.index');
    Route::get('/turnamen/quiz/history', [QuizRoomController::class, 'history'])->name('learning.quiz.history');
    Route::post('/turnamen/quiz', [QuizRoomController::class, 'store'])->name('learning.quiz.store');
    Route::post('/turnamen/quiz/join', [QuizRoomController::class, 'join'])->name('learning.quiz.join');
    Route::get('/turnamen/quiz/{room}', [QuizRoomController::class, 'show'])->name('learning.quiz.room');
    Route::post('/turnamen/quiz/{room}/questions', [QuizRoomController::class, 'addQuestion'])->name('learning.quiz.questions.store');
    Route::post('/turnamen/quiz/{room}/questions/{question}', [QuizRoomController::class, 'updateQuestion'])->name('learning.quiz.questions.update');
    Route::post('/turnamen/quiz/{room}/start', [QuizRoomController::class, 'start'])->name('learning.quiz.start');
    Route::post('/turnamen/quiz/{room}/finish', [QuizRoomController::class, 'finish'])->name('learning.quiz.finish');
    Route::get('/api/turnamen/quiz/{room}/state', [QuizRoomController::class, 'state'])->name('api.quiz.state');
    Route::post('/api/turnamen/quiz/{room}/answer', [QuizRoomController::class, 'answer'])->name('api.quiz.answer');
    Route::post('/turnamen/cepat', [LearningDashboardController::class, 'submitTournament'])->name('learning.tournament.submit');
    Route::get('/api/turnamen/modes', [LearningDashboardController::class, 'apiGameModes'])->name('api.tournament.modes');
    Route::get('/api/turnamen/leaderboard', [LearningDashboardController::class, 'apiTournamentLeaderboard'])->name('api.tournament.leaderboard');
    Route::get('/dashboard/parts/{part}', [LearningDashboardController::class, 'showPart'])->name('learning.parts.show');
    Route::get('/dashboard/parts/{part}/levels/{level}', [LearningDashboardController::class, 'showLevel'])->name('learning.levels.show');
    Route::post('/dashboard/parts/{part}/levels/{level}/complete', [LearningDashboardController::class, 'completeLevel'])->name('learning.levels.complete');
});
