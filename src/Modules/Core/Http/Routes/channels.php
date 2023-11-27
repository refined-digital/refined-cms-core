<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('refinedCMS.media.updated', function($user) {
    return $user->user_level_id < 3;
});
