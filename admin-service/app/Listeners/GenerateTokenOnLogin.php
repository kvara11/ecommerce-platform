<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class GenerateTokenOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        $user->tokens()->delete();

        $token = $user->createToken('personal-access-token')->plainTextToken;

        session(['api_token' => $token]);
    }
}
