<div>
    @if($error)
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}
        </div>
    @endif

    <form wire:submit="submit">
        <div class="input-group mb-3">
            <input
                type="number"
                wire:model="mobile"
                class="form-control"
                inputmode="numeric"
                pattern="[0-9]*"
                autocomplete="off"
                placeholder="Mobile number"
                autofocus
            >
            <div class="input-group-append">
                <div class="input-group-text"><i class="fas fa-mobile-alt"></i></div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="fas fa-paper-plane mr-1"></i> Send OTP</span>
                    <span wire:loading><i class="fas fa-spinner fa-spin mr-1"></i> Sending…</span>
                </button>
            </div>
        </div>
    </form>
</div>
