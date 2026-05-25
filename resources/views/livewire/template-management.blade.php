<div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3 px-3 pt-3">
        <h5 class="mb-0"><i class="fas fa-clipboard-list mr-1"></i> SMS Templates</h5>
        @unless ($showForm)
            <button type="button" class="btn btn-primary btn-sm" wire:click="newTemplate">
                <i class="fas fa-plus mr-1"></i> New Template
            </button>
        @endunless
    </div>

    @if ($showForm)
        <div class="card card-outline card-primary mx-3 mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    {{ $editingId ? 'Edit Template' : 'New Template' }}
                </h6>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   wire:model.defer="name" maxlength="100">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label>Sender ID <small class="text-muted">(blank = shared)</small></label>
                            <input type="text" class="form-control @error('sender_id') is-invalid @enderror"
                                   wire:model.defer="sender_id" maxlength="20">
                            @error('sender_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label>Slug <small class="text-muted">(optional, for ?template=)</small></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                   wire:model.defer="slug" maxlength="60" placeholder="order_ready">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Body</label>
                        <textarea class="form-control @error('body') is-invalid @enderror"
                                  wire:model.defer="body" rows="4" maxlength="1000"></textarea>
                        @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save mr-1"></i> {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <table class="table table-bordered table-striped table-hover mb-0">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th><i class="fas fa-tag mr-1"></i> Name</th>
                <th><i class="fas fa-id-badge mr-1"></i> Sender ID</th>
                <th>Slug</th>
                <th>Body</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($templates as $tpl)
                <tr>
                    <td>{{ $tpl->id }}</td>
                    <td>{{ $tpl->name }}</td>
                    <td>
                        @if ($tpl->sender_id)
                            <span class="badge badge-primary">{{ $tpl->sender_id }}</span>
                        @else
                            <span class="badge badge-secondary">shared</span>
                        @endif
                    </td>
                    <td>
                        @if ($tpl->slug)
                            <code>{{ $tpl->slug }}</code>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td><small>{{ \Illuminate\Support\Str::limit($tpl->body, 80) }}</small></td>
                    <td class="text-right">
                        <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $tpl->id }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                                wire:click="delete({{ $tpl->id }})"
                                onclick="return confirm('Delete this template?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-clipboard fa-2x mb-2 d-block"></i> No templates yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-3 py-2">
        {{ $templates->links() }}
    </div>
</div>
