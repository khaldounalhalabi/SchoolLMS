<?php

namespace App\Services;

use App\Mail\PasswordResetOtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetOtpService
{
    private const TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    public function send(string $email): void
    {
        $otp = (string) random_int(100000, 999999);

        Cache::put($this->key($email), [
            'hash' => Hash::make($otp),
            'attempts' => 0,
        ], now()->addMinutes(self::TTL_MINUTES));

        Mail::to($email)->send(new PasswordResetOtpMail($otp, self::TTL_MINUTES));
    }

    /**
     * Verify the OTP and, if valid, consume it so it cannot be reused.
     */
    public function verify(string $email, string $otp): bool
    {
        $key = $this->key($email);
        $entry = Cache::get($key);

        if (! $entry || $entry['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($key);

            return false;
        }

        if (! Hash::check($otp, $entry['hash'])) {
            $entry['attempts']++;
            Cache::put($key, $entry, now()->addMinutes(self::TTL_MINUTES));

            return false;
        }

        Cache::forget($key);

        return true;
    }

    private function key(string $email): string
    {
        return 'password_reset_otp:'.strtolower($email);
    }
}
