<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    // الشرط: نسمح للمستخدم بالاشتراك في القناة فقط إذا كان هو صاحب الرسالة
    // أو إذا كان هو الشخص المستقبِل لها.
    return (int) $user->id === (int) $receiverId;
});
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
