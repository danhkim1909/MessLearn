<?php

use App\Http\Controllers\Guest\HomeController;
use App\Http\Controllers\Guest\AuthController;
use App\Http\Controllers\User\ChatBoardController;
use App\Http\Controllers\User\FriendshipController;
use App\Http\Controllers\User\ConversationController;
use App\Http\Controllers\User\ProfileController;
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
        Route::post('/cancel/{id}', [FriendshipController::class, 'cancelRequest'])->name('cancel');
        Route::post('/reject/{id}', [FriendshipController::class, 'rejectRequest'])->name('reject');
        Route::post('/unfriend/{friendId}', [FriendshipController::class, 'unfriend'])->name('unfriend');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    Route::prefix('conversation')->name('conversation.')->group(function () {
        Route::post('/group', [ConversationController::class, 'storeGroup'])->name('store-group');
        Route::get('/{conversation}/members', [ConversationController::class, 'getMembers'])->name('members');
        Route::get('/{conversation}/members/available-friends', [ConversationController::class, 'getAvailableFriends'])->name('members.available-friends');
        Route::post('/{conversation}/members/add', [ConversationController::class, 'addMembers'])->name('members.add');
        Route::post('/{conversation}/members/remove', [ConversationController::class, 'removeMember'])->name('members.remove');
        Route::post('/{conversation}/leave', [ConversationController::class, 'leaveGroup'])->name('leave');
        Route::post('/{conversation}/message', [\App\Http\Controllers\User\MessageController::class, 'store'])->name('message.store');
        Route::get('/{conversation}/messages/load-more', [\App\Http\Controllers\User\MessageController::class, 'loadMore'])->name('message.load-more');
        Route::get('/{conversation}/messages/search', [\App\Http\Controllers\User\MessageController::class, 'search'])->name('message.search');
        Route::post('/{conversation}/reaction/{message}', [\App\Http\Controllers\User\MessageController::class, 'toggleReaction'])->name('message.reaction');
        Route::post('/{conversation}/message/{message}/pin', [\App\Http\Controllers\User\MessageController::class, 'togglePin'])->name('message.pin');
        Route::post('/{conversation}/message/{message}/forward', [\App\Http\Controllers\User\MessageController::class, 'forward'])->name('message.forward');
        Route::post('/{conversation}/message/{message}/unsend', [\App\Http\Controllers\User\MessageController::class, 'unsend'])->name('message.unsend');
        Route::post('/{conversation}/event/create', [\App\Http\Controllers\User\MessageController::class, 'createEvent'])->name('event.create');
        Route::post('/{conversation}/event/{message}/join', [\App\Http\Controllers\User\MessageController::class, 'toggleJoinEvent'])->name('event.join');
        Route::post('/{conversation}/game/dice', [\App\Http\Controllers\User\MessageController::class, 'rollDice'])->name('game.dice');
        Route::post('/{conversation}/game/rps/create', [\App\Http\Controllers\User\MessageController::class, 'createRps'])->name('game.rps.create');
        Route::post('/{conversation}/game/rps/{message}/play', [\App\Http\Controllers\User\MessageController::class, 'playRps'])->name('game.rps.play');
        Route::post('/{conversation}/quiz', [\App\Http\Controllers\User\QuizController::class, 'store'])->name('quiz.store');
        Route::get('/{conversation}/quiz/{form}', [\App\Http\Controllers\User\QuizController::class, 'show'])->name('quiz.show');
        Route::post('/{conversation}/quiz/{form}/submit', [\App\Http\Controllers\User\QuizController::class, 'submit'])->name('quiz.submit');
        Route::get('/{conversation}/quiz/{form}/results', [\App\Http\Controllers\User\QuizController::class, 'results'])->name('quiz.results');

        // Cuoc goi va phong hop truc tuyen WebRTC
        Route::post('/{conversation}/meeting/start', [\App\Http\Controllers\User\MeetingController::class, 'start'])->name('meeting.start');
        Route::post('/{conversation}/meeting/signal', [\App\Http\Controllers\User\MeetingController::class, 'signal'])->name('meeting.signal');
        Route::post('/{conversation}/meeting/leave', [\App\Http\Controllers\User\MeetingController::class, 'leave'])->name('meeting.leave');
        Route::post('/{conversation}/meeting/reject', [\App\Http\Controllers\User\MeetingController::class, 'reject'])->name('meeting.reject');
    });
});