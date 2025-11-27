<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::routes();

Broadcast::channel('private-user-{id}', function ($user, $id) {
    Log::info("🔐 CHANNEL AUTH DEBUG", [
        'user_id' => $user->id,
        'user_id_type' => gettype($user->id),
        'channel_id' => $id, 
        'channel_id_type' => gettype($id),
        'comparison' => (int) $user->id === (int) $id
    ]);

    return (int) $user->id === (int) $id;
});