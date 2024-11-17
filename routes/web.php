<?php

use Illuminate\Support\Facades\Route;
use JacobTilly\LaraFort\Http\Controllers\OAuthController;

Route::get('/fortnox/callback', [OAuthController::class, 'callback'])
    ->name('larafort.callback');
