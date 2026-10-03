<?php

use App\Http\Controllers\Guest\HomeController;
use App\Http\Controllers\Guest\AuthController;
use App\Http\Controllers\User\ChatBoardController;
use App\Http\Middleware\Authenication\CheckLoginMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/auth', [AuthController::class, 'index'])->name('auth');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('app')->middleware(CheckLoginMiddleware::class)->name('app.')->group(function () {
    Route::get('/', [ChatBoardController::class, 'index'])->name('chat-board');
});