@extends('admin.auth-layout')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>SMS</b> Portal</a>
    </div>
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <p class="text-muted mb-0">Sign in to SMS Portal</p>
        </div>
        <div class="card-body text-center">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <a href="{{ route('auth.redirect') }}" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt mr-2"></i> Login with SecureIVS
            </a>
        </div>
    </div>
</div>
@endsection
