<?php

namespace App\Listeners;

use App\Models\LoginHistory;
use Illuminate\Auth\Events\Login;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogin
{
    /**
     * Handle the successful login event.
     */
    public function handle(Login $event): void
    {
        LoginHistory::create([
            'user_id'    => $event->user->id,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'location'   => 'Dhaka, Bangladesh',
            // Saves the current time in Asia/Dhaka timezone
            'login_at'   => Carbon::now('Asia/Dhaka'),
        ]);
    }
}
