<?php

use Illuminate\Support\Facades\Broadcast;

// 用户私有频道
Broadcast::channel('User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
