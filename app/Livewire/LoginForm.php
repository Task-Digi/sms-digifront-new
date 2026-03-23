<?php

namespace App\Livewire;

use App\Services\SmsService;
use Livewire\Component;
use Illuminate\Support\Facades\RateLimiter;

class LoginForm extends Component
{
    public string $mobile = '';
    public string $error = '';

    public function submit(SmsService $smsService): void
    {
        $this->error = '';

        if (empty($this->mobile)) {
            $this->error = 'Mobile number is required.';
            return;
        }

        $users = config('settings.users');

        if (!isset($users[$this->mobile])) {
            $this->error = 'Mobile number not found.';
            return;
        }

        // Max 3 OTP requests per mobile per hour
        $throttleKey = 'otp-request:' . $this->mobile;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            $this->error = "Too many OTP requests. Try again in {$minutes} minute(s).";
            return;
        }
        RateLimiter::hit($throttleKey, 3600);

        // Generate OTP — use test code if test mode is enabled
        $otp = env('OTP_TEST_MODE')
            ? env('OTP_TEST_CODE', '1234')
            : str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        // Store mobile, OTP and expiry in session
        session()->put('mobile', $this->mobile);
        session()->put('otp', $otp);
        session()->put('otp_expires_at', now()->addMinutes(5)->timestamp);
        session()->put('otp_attempts', 0);

        // Only send SMS in production
        if (env('OTP_TEST_MODE') !== 'true') {
            $smsService->send('Verify', $this->mobile, "Your OTP code is: {$otp}. Valid for 5 minutes.");
        }

        $this->redirect(route('admin.login'), navigate: false);
    }

    public function render()
    {
        return view('livewire.login-form');
    }
}
