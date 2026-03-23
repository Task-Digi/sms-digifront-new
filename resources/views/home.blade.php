@extends('admin.layout')

@section('title', 'Send SMS')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-paper-plane mr-1"></i> Compose SMS
                </h3>
            </div>
            <div class="card-body">
                <livewire:sms-send-form />
            </div>
        </div>
    </div>
</div>
@endsection
