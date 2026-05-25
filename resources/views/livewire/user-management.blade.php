<div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3 px-3 pt-3">
        <h5 class="mb-0"><i class="fas fa-users mr-1"></i> Users</h5>
        @unless ($showForm)
            <button type="button" class="btn btn-primary btn-sm" wire:click="newUser">
                <i class="fas fa-plus mr-1"></i> New User
            </button>
        @endunless
    </div>

    @if ($showForm)
        <div class="card card-outline card-primary mx-3 mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    {{ $editingId ? 'Edit User' : 'New User' }}
                </h6>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label>Mobile</label>
                            <input type="text" class="form-control @error('mobile') is-invalid @enderror" wire:model.defer="mobile">
                            @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-2">
                            <label>Sender ID</label>
                            <input type="text" class="form-control @error('sender_id') is-invalid @enderror" wire:model.defer="sender_id">
                            @error('sender_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-2">
                            <label>Monthly Limit
                                <i class="fas fa-info-circle text-muted" title="Max SMS segments per month. Leave blank for unlimited. 0 blocks all sending."></i>
                            </label>
                            <input type="number" min="0" class="form-control @error('monthly_sms_limit') is-invalid @enderror"
                                   wire:model.defer="monthly_sms_limit" placeholder="Unlimited">
                            @error('monthly_sms_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-1 d-flex align-items-end">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="is_active" wire:model.defer="is_active">
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="form-group col-md-1 d-flex align-items-end">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="is_admin" wire:model.defer="is_admin">
                                <label class="custom-control-label" for="is_admin">Admin</label>
                            </div>
                        </div>
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
                <th><i class="fas fa-user mr-1"></i> Name</th>
                <th><i class="fas fa-mobile-alt mr-1"></i> Mobile</th>
                <th><i class="fas fa-id-badge mr-1"></i> Sender ID</th>
                <th>Role</th>
                <th>Status</th>
                <th>Monthly Usage</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->mobile }}</td>
                    <td><span class="badge badge-primary">{{ $user->sender_id }}</span></td>
                    <td>
                        @if ($user->is_admin)
                            <span class="badge badge-danger">Admin</span>
                        @else
                            <span class="badge badge-info">User</span>
                        @endif
                    </td>
                    <td>
                        @if ($user->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @php($used = $user->smsUsageThisMonth())
                        @if ($user->monthly_sms_limit === null)
                            <span class="badge badge-light">{{ $used }} / &infin;</span>
                        @else
                            @php($pct = $user->monthly_sms_limit > 0 ? ($used / $user->monthly_sms_limit) : 1)
                            <span class="badge {{ $pct >= 1 ? 'badge-danger' : ($pct >= 0.8 ? 'badge-warning' : 'badge-info') }}">
                                {{ $used }} / {{ $user->monthly_sms_limit }}
                            </span>
                        @endif
                    </td>
                    <td class="text-right">
                        <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $user->id }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                                wire:click="delete({{ $user->id }})"
                                onclick="return confirm('Delete this user?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-user-slash fa-2x mb-2 d-block"></i> No users yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-3 py-2">
        {{ $users->links() }}
    </div>
</div>
