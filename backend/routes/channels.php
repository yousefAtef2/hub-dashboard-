<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| room.{roomId} is a presence channel: only the two (or more) participants
| who were authorized to join that call room may subscribe. Replace the
| naive check below with your real auth/session logic (e.g. verify the
| user has an active booking/session for this room id).
*/
Broadcast::channel('room.{roomId}', function ($user, string $roomId) {
    // TODO: replace with real authorization (e.g. check session/DB record
    // that ties the authenticated user/agent to this room id).
    return [
        'id'   => $user->id ?? request()->query('participant_id', uniqid()),
        'name' => $user->name ?? 'Participant',
    ];
});
