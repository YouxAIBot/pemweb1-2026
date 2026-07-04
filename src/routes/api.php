<?php

use App\Http\Controllers\Frontend\PremiumController;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/premium/notification', [PremiumController::class, 'midtransNotification'])
    ->name('api.midtrans.premium.notification');
