@extends('admin.auth-layout')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>SMS</b> Portal</a>
    </div>
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <p class="text-muted mb-0">Enter your mobile number to receive OTP</p>
        </div>
        <div class="card-body">
            <livewire:login-form />
        </div>
    </div>
</div>
@endsection
