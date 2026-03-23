<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\RateLimiter;

class OtpForm extends Component
{
    public string $code1 = '';
    public string $code2 = '';
    public string $code3 = '';
    public string $code4 = '';
    public string $error = '';

    public function submit(): void
    {
        $this->error = '';

        $mobile = session('mobile');
        if (!$mobile) {
            $this->redirect(route('admin.login'), navigate: false);
            return;
        }

        // Max 5 attempts before lockout
        $throttleKey = 'otp-attempt:' . $mobile;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            session()->flush();
            $this->error = "Too many failed attempts. Try again in {$minutes} minute(s).";
            return;
        }

        // Check OTP expiry
        $expiresAt = session('otp_expires_at');
        if (!$expiresAt || now()->timestamp > $expiresAt) {
            session()->flush();
            $this->error = 'OTP has expired. Please request a new one.';
            $this->redirect(route('admin.login'), navigate: false);
            return;
        }

        $enteredCode = $this->code1 . $this->code2 . $this->code3 . $this->code4;
        $storedOtp   = session('otp');
        $users       = config('settings.users');

        if (!isset($users[$mobile])) {
            session()->flush();
            $this->redirect(route('admin.login'), navigate: false);
            return;
        }

        if ($enteredCode === $storedOtp) {
            // Clear rate limiter and OTP data on success
            RateLimiter::clear($throttleKey);
            $user = $users[$mobile];
            session()->forget(['otp', 'otp_expires_at', 'otp_attempts']);
            session()->put('login_status', true);
            session()->put('user', $user);
            $this->redirect(route('home'), navigate: false);
        } else {
            RateLimiter::hit($throttleKey, 900); // 15 min lockout window
            $remaining = 5 - RateLimiter::attempts($throttleKey);
            $this->error = 'Invalid OTP.' . ($remaining > 0 ? " {$remaining} attempt(s) remaining." : '');
            $this->code1 = $this->code2 = $this->code3 = $this->code4 = '';
        }
    }

    public function render()
    {
        return view('livewire.otp-form');
    }
}
