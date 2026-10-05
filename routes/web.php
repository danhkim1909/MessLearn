<?php

use App\Http\Controllers\Guest\HomeController;
use App\Http\Controllers\Guest\AuthController;
use App\Http\Controllers\User\ChatBoardController;
use App\Http\Controllers\User\FriendshipController;
use App\Http\Controllers\User\ConversationController;
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
    Route::get('/c/{conversation}', [ChatBoardController::class, 'show'])->name('chat-board.show');

    Route::prefix('friend')->name('friend.')->group(function () {
        Route::get('/search', [FriendshipController::class, 'search'])->name('search');
        Route::post('/send', [FriendshipController::class, 'sendRequest'])->name('send');
        Route::post('/accept/{id}', [FriendshipController::class, 'acceptRequest'])->name('accept');
    });

    Route::prefix('conversation')->name('conversation.')->group(function () {
        Route::post('/group', [ConversationController::class, 'storeGroup'])->name('store-group');
        Route::post('/{conversation}/message', [\App\Http\Controllers\User\MessageController::class, 'store'])->name('message.store');
        Route::post('/{conversation}/reaction/{message}', [\App\Http\Controllers\User\MessageController::class, 'toggleReaction'])->name('message.reaction');
        Route::post('/{conversation}/game/dice', [\App\Http\Controllers\User\MessageController::class, 'rollDice'])->name('game.dice');
        Route::post('/{conversation}/game/rps/create', [\App\Http\Controllers\User\MessageController::class, 'createRps'])->name('game.rps.create');
        Route::post('/{conversation}/game/rps/{message}/play', [\App\Http\Controllers\User\MessageController::class, 'playRps'])->name('game.rps.play');
        Route::post('/{conversation}/quiz', [\App\Http\Controllers\User\QuizController::class, 'store'])->name('quiz.store');
        Route::get('/{conversation}/quiz/{form}', [\App\Http\Controllers\User\QuizController::class, 'show'])->name('quiz.show');
        Route::post('/{conversation}/quiz/{form}/submit', [\App\Http\Controllers\User\QuizController::class, 'submit'])->name('quiz.submit');
        Route::get('/{conversation}/quiz/{form}/results', [\App\Http\Controllers\User\QuizController::class, 'results'])->name('quiz.results');
    });
});