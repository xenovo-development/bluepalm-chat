<?php

use App\Http\Controllers\ConversationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', config('jetstream.auth_session'),])->group(function () {
    Route::get('/', [ConversationController::class,'index'])->name('dashboard');
    Route::get('/chat/messages/{source}/{id}', [\App\Http\Controllers\ConversationController::class, 'messages'])
        ->whereIn('source', ['local','convolite'])
        ->name('chat.messages');
});

Route::get('/whoami', function () {
    return [
        'check' => \Illuminate\Support\Facades\Auth::check(),
        'user'  => optional(auth()->user())->only(['id','name','email']),
    ];
});
