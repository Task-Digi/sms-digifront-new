@extends('admin.auth-layout')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>SMS</b> Portal</a>
    </div>
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <p class="text-muted mb-0">Enter the 4-digit OTP</p>
            @if(env('OTP_TEST_MODE'))
                <span class="badge badge-warning mt-1">
                    <i class="fas fa-flask mr-1"></i> Test mode — use <strong>{{ env('OTP_TEST_CODE', '1234') }}</strong>
                </span>
            @endif
        </div>
        <div class="card-body">
            <livewire:otp-form />
        </div>
    </div>
</div>
@endsection
