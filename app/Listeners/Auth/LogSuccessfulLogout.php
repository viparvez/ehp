<?php

namespace App\Listeners\Auth;

use Illuminate\Auth\Events\Logout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class LogSuccessfulLogout
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
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        DB::table('eventlogger')->insert(
            [
                'event' => 'Logout',
                'description' => "Request from". \Request::ip(),
                'timestamp' => date('Y-m-d h:i:s'),
                'userid' => $event->user->id,
            ]
        );
    }
}
