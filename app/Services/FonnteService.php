<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    public static function sendMessage($phone, $message)
    {
        if (!$phone) return false;

        $formattedPhone = preg_replace('/[^0-9]/', '', $phone);

        return Http::withHeaders([
            'Authorization' => env('FONNTE_TOKEN'),
        ])->post('https://api.fonnte.com/send', [
            'target' => $formattedPhone,
            'message' => $message,
        ])->json();
    }
}
