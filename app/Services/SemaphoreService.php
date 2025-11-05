<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SemaphoreService{
        protected $apiKey;
        protected $senderName;

        public function __construct()
        {
            $this->apiKey = env('SEMAPHORE_API_KEY');
            $this->senderName = env('SEMAPHORE_SENDER_NAME', 'CPMSTubod');
        }

        public function sendSMS($to, $message)
        {
            $response = Http::post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => $this->apiKey,
                'number' => $to,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            return $response->json();
        }
}