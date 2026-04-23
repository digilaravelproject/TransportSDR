<?php

namespace App\Services;

use App\Models\{Otp, User};
use App\Mail\{LoginOtpMail, ForgotPasswordOtpMail, RegistrationOtpMail};
use Illuminate\Support\Facades\Mail;

class OtpService
{
    private function generate(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function create(string $email, string $type): string
    {
        // Purane OTP delete
        Otp::where('email', $email)->where('type', $type)->delete();

        //$otp = $this->generate();
        $otp = '123456';
        Otp::create([
            'email'      => $email,
            'otp'        => $otp,
            'type'       => $type,
            'is_used'    => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }

    public function verify(string $email, string $otp, string $type): array
    {
        $record = Otp::where('email', $email)
            ->where('type', $type)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$record) {
            return ['valid' => false, 'message' => 'Invalid OTP. Please request a new OTP.'];
        }

        if ($record->isExpired()) {
            return ['valid' => false, 'message' => 'OTP has expired. Please request a new OTP.'];
        }

        if ($record->otp !== $otp) {
            return ['valid' => false, 'message' => 'Incorrect OTP. Please check and try again.'];
        }

        $record->update(['is_used' => true]);

        return ['valid' => true];
    }

    public function check(string $email, string $otp, string $type): array
    {
        $record = Otp::where('email', $email)
            ->where('type', $type)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$record) {
            return ['valid' => false, 'message' => 'Invalid OTP. Please request a new OTP.'];
        }

        if ($record->isExpired()) {
            return ['valid' => false, 'message' => 'OTP has expired. Please request a new OTP.'];
        }

        if ($record->otp !== $otp) {
            return ['valid' => false, 'message' => 'Incorrect OTP. Please check and try again.'];
        }

        return ['valid' => true];
    }

    public function sendLoginOtp(User $user): void
    {
        $otp = $this->create($user->email, 'login');
        Mail::to($user->email)->send(new LoginOtpMail($otp, $user->name));
    }

    public function sendForgotOtp(User $user): void
    {
        $otp = $this->create($user->email, 'forgot_password');
        Mail::to($user->email)->send(new ForgotPasswordOtpMail($otp, $user->name));
    }

    // NEW — Registration OTP
    public function sendRegistrationOtp(string $email, string $name): void
    {
        $otp = $this->create($email, 'registration');
        Mail::to($email)->send(new RegistrationOtpMail($otp, $name));
    }

    public function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        return substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3)) . '@' . $domain;
    }
}
