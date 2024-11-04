<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ReverbService
{
    protected $endpoint;
    protected $apiKey;

    public function __construct()
    {
        $this->endpoint = config('reverb.endpoint');
        $this->apiKey = config('reverb.api_key');
    }

    /**
     * Відправка повідомлення через Reverb
     *
     * @param string $event
     * @param array $data
     * @return void
     */
    public function sendNotification(string $event, array $data): void
    {
        Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, [
            'event' => $event,
            'data' => $data,
        ]);
    }
}
