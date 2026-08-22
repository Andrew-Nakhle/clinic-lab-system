<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
    // الشرط: نسمح للمستخدم بالاشتراك في القناة فقط إذا كان هو صاحب الرسالة
    // أو إذا كان هو الشخص المستقبِل لها.
});


Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
