<div x-data="{
    advance(current, next) {
        if ($refs[current].value.length === 1 && $refs[next]) {
            $refs[next].focus();
        }
    }
}">
    @if($error)
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}
        </div>
    @endif

    <form wire:submit="submit">
        <div class="input-group mb-3 justify-content-center">
            @foreach(['code1' => 'code2', 'code2' => 'code3', 'code3' => 'code4', 'code4' => null] as $field => $next)
                <input
                    type="password"
                    wire:model="{{ $field }}"
                    x-ref="{{ $field }}"
                    @if($next) x-on:input="advance('{{ $field }}','{{ $next }}')" @endif
                    inputmode="numeric"
                    maxlength="1"
                    class="form-control text-center mx-1"
                    style="max-width: 55px; font-size: 1.4rem; letter-spacing: 2px;"
                    autocomplete="off"
                    @if($field === 'code1') autofocus @endif
                >
            @endforeach
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="fas fa-unlock mr-1"></i> Verify OTP</span>
                    <span wire:loading><i class="fas fa-spinner fa-spin mr-1"></i> Verifying…</span>
                </button>
            </div>
        </div>
        <p class="mt-3 mb-0 text-center">
            <a href="{{ route('admin.login.back') }}"><i class="fas fa-arrow-left mr-1"></i> Back</a>
        </p>
    </form>
</div>
