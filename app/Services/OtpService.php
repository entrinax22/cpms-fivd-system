<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OtpService
{
    protected $ttlSeconds = 600; // 10 minutes
    protected $maxAttempts = 5;
    protected $semaphore;

    public function __construct(SemaphoreService $semaphore)
    {
        $this->semaphore = $semaphore;
    }

    protected function key(string $phone): string
    {
        return "otp_phone_" . $phone;
    }

    protected function attemptsKey(string $phone): string
    {
        return "otp_attempts_" . $phone;
    }

    public function generateOtp(string $phone): string
    {
        $otp = (string) random_int(100000, 999999);
        Cache::put($this->key($phone), $otp, $this->ttlSeconds);
        Cache::put($this->attemptsKey($phone), 0, $this->ttlSeconds);
        return $otp;
    }

    public function sendOtp(string $phone): array
    {
        $otp = $this->generateOtp($phone);
        $message = "Your CPMs OTP is: {$otp}. It expires in 10 minutes.";
        return $this->semaphore->sendSMS($phone, $message);
    }

    public function validateOtp(string $phone, string $otp): bool
    {
        $stored = Cache::get($this->key($phone));
        if (!$stored) {
            return false;
        }

        $attempts = Cache::get($this->attemptsKey($phone), 0);
        if ($attempts >= $this->maxAttempts) {
            return false;
        }

        if (hash_equals($stored, $otp)) {
            // consume
            Cache::forget($this->key($phone));
            Cache::forget($this->attemptsKey($phone));
            return true;
        }

        Cache::increment($this->attemptsKey($phone));
        return false;
    }

    public function clearOtp(string $phone): void
    {
        Cache::forget($this->key($phone));
        Cache::forget($this->attemptsKey($phone));
    }
}
