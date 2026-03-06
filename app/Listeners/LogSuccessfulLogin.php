<?php

namespace App\Listeners;

use App\Models\LoginHistory;
use Illuminate\Auth\Events\Login;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Http;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $actualIp = Request::ip();
        $lookupIp = ($actualIp === '127.0.0.1' || $actualIp === '::1')
            ? '103.145.118.20'
            : $actualIp;
        $locationLabel = 'Unknown';
        $timezoneLabel = 'Unknown';
        $calculationTimezone = 'Asia/Dhaka';
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$lookupIp}");

            if ($response->successful() && $response['status'] === 'success') {
                $locationLabel = ($response['city'] ?? 'Unknown') . ', ' . ($response['country'] ?? 'Unknown');
                if (!empty($response['timezone'])) {
                    $timezoneLabel = $response['timezone'];
                    $calculationTimezone = $response['timezone'];
                }
            }
        } catch (\Exception $e) {
            // If API fails, variables stay as "Unknown"
        }

        LoginHistory::create([
            'user_id'    => $event->user->id,
            'ip_address' => $actualIp,
            'user_agent' => Request::userAgent(),
            'location'   => $locationLabel,
            'timezone'   => $timezoneLabel,
            'login_at'   => Carbon::now($calculationTimezone),
        ]);
    }
}
