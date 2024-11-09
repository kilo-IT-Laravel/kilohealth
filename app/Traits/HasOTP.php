<?php

namespace App\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;

trait HasOTP
{
    public function sendOTP(string $type = 'verification'): bool
    {
        try {
            $otp = $this->generateOTP();

            $this->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(5),
            ]);

            return $this->sendOTPEmail($otp, $type);
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    private function generateOTP(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendOTPEmail(string $otp, string $type): bool
    {
        try {
            $subject = $type === 'verification'
                ? 'Email Verification OTP'
                : 'Reset Password OTP';

            Mail::raw(
                "Your {$type} OTP is: {$otp}",
                function ($message) use ($subject) {
                    $message->to($this->email)
                        ->subject($subject);
                }
            );

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function verifyOTP(string $otp): bool
    {
        return $this->otp === $otp &&
            $this->otp_expires_at &&
            Carbon::parse($this->otp_expires_at)->isFuture();
    }

    public function canRequestOTP(): bool
    {
        if (!$this->otp_expires_at) {
            return true;
        }

        return Carbon::parse($this->otp_expires_at)
            ->diffInMinutes(Carbon::now()) >= 1;
    }

    public function getOTPCooldownSeconds(): int
    {
        if (!$this->otp_expires_at) {
            return 0;
        }

        $diff = 60 - Carbon::parse($this->otp_expires_at)
            ->diffInSeconds(Carbon::now());

        return max(0, $diff);
    }

    public function clearOTP(): void
    {
        $this->update([
            'otp' => null,
            'otp_expires_at' => null
        ]);
    }
}
