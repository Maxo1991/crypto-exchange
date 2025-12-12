<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Ovde definišeš private i presence kanale. Autentikacija se proverava
| prilikom povezivanja sa kanalima.
|
*/

Broadcast::channel('user.{id}', function ($user, $id) {
    // dozvoli pristup samo ako je user logovan i ID se poklapa
    logger('Broadcast auth', ['user' => $user, 'id' => $id]);
    return (int) $user->id === (int) $id;
});
