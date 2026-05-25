@extends('admin.layout')

@section('title', 'Templates')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-body p-0">
                <livewire:template-management />
            </div>
        </div>
    </div>
</div>
@endsection
