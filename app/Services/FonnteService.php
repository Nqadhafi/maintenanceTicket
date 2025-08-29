<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    public function send(string $noWa, string $message): bool
    {
        if (!$noWa) return false;
        $resp = Http::withHeaders(['Authorization' => env('FONNTE_TOKEN')])
            ->asForm()
            ->post('https://api.fonnte.com/send', [
                'target' => $noWa,
                'message' => $message,
            ]);
        return $resp->successful();
    }
}
