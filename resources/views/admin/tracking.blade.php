@extends('admin.layout')

@section('title', 'SMS Tracking')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-alt mr-1"></i> Sent Messages
                </h3>
            </div>
            <div class="card-body p-0">
                <livewire:sms-tracking />
            </div>
        </div>
    </div>
</div>
@endsection
