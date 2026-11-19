<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WaNotif
{
    private function client()
    {
        return Http::baseUrl(rtrim(config('wa.url'), '/'))
            ->withHeaders([
                'X-WA-Token' => config('wa.token'),
            ])
            ->acceptJson()
            ->timeout(15);
    }

    public function status(): array
    {
        return $this->client()
            ->get('/api/status')
            ->throw()
            ->json();
    }

    public function send(
        string $to,
        string $template,
        array $data = [],
        ?string $idempotencyKey = null
    ): array {
        $payload = [
            'idempotency_key' => $idempotencyKey ?? (string) Str::uuid(),
            'to' => $to,
            'template' => $template,
            'data' => $data,
        ];

        return $this->client()
            ->post('/api/messages', $payload)
            ->throw()
            ->json();
    }
}