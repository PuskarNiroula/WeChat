<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\ExtraController;
use App\Http\Controllers\Api\GroupChatApiController;
use App\Http\Controllers\Api\KeyController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;


Route::get('/test', function () {
    return response()->json(['message' => 'API works']);
});
Route::controller(AuthController::class)->group(function () {
    Route::post('/api/login', 'login')->name('api.login');
    Route::post('/sendPasswordResetLinkEmail', 'sendResetLink')->name('password.email');
    Route::post('/api/resetPassword/{token}/{email}', 'resetPassword')->name('password.update');

});
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::post('/api/user/public-key', 'publicKey')->name('getPublicKey');
        Route::post('/api/logout', 'logout')->name('api.logout');
        Route::post('/api/change-password', 'changePassword')->name('api.changePassword');
        Route::post('api/logout','logoutAllDevices')->name('api.logout');
    });

    Route::get('/api/user/{receiverId}/public-key',[KeyController::class,'getPublicKey'])->name('getPublicKey');

    Route::controller(MessageController::class)->group(function () {
        Route::get('/getMessages/{id}', 'getChunkMessages')->name('getMessages');
        Route::post('/sendMessage', 'sendMessage')->name('sendMessage');
    });

    Route::controller(ExtraController::class)->group(function () {
        Route::get('/search/{string}', 'search')->name('searchUsers');
        Route::get("/getSidebarMembers","getSidebar")->name("getSidebarMembers");
    });

    Route::post('/updateProfile',[ProfileController::class,'updateProfile'])->name('updateProfile');

    Route::controller(ConversationController::class)->group(function () {
        Route::get('/api/conversation/{receiverId}/check','checkConversation')->name('checkConversation');
        Route::post('/api/conversation/create-private-conversation','createPrivateConversation')->name('createPrivateConversation');
        Route::get('/api/conversation/{conversationId}/key','getRoomKey')->name('getRoomKey');
        Route::get('/api/conversation/{id}/meta','getConversationMeta')->name('getConversationMeta');
        Route::post('/api/group/{conId}/update','updateConversation')->name('updateGroupChat');
        Route::get('/api/conversation/{conId}/latest-key','getLatestKey')->name('getLatestKey');
    });

    Route::controller(GroupChatApiController::class)->group(function () {
        Route::post('/api/group-chat/create','createGroupChat')->name('createGroupChat');
        Route::get('/api/group-chat/new-member/{name}/search','searchNewMember')->name('searchNewMember');
        Route::get('/api/group-chat/{conversationId}/get-old-members','getGroupMembers')->name('getOldMembers');
        Route::post('/api/group-chat/add-members','addNewMembers')->name('addNewMembers');
        Route::post('/api/group-chat/remove-members','removeMembers')->name('removeMembers');
        Route::post('/api/group-chat/leave-group','leaveGroupChat')->name('leaveGroupChat');
    });


});


