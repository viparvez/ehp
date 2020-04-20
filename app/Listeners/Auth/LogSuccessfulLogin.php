<?php

namespace App\Listeners\Auth;

use Illuminate\Auth\Events\Login;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;


class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        DB::table('eventlogger')->insert(
            [
                'event' => 'Login',
                'description' => "Request from". \Request::ip(),
                'timestamp' => date('Y-m-d h:i:s'),
                'userid' => $event->user->id,
            ]
        );
    }
}
