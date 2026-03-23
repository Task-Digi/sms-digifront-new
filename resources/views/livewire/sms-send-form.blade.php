<div>
    {{-- Alerts --}}
    @if($successMessage)
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> {{ $successMessage }}
        </div>
    @endif

    @if($error)
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}
        </div>
    @endif

    @if($errors->any())
        @foreach($errors->all() as $err)
            <div class="alert alert-danger alert-dismissible py-2">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ $err }}
            </div>
        @endforeach
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'single' ? 'active' : '' }}"
               wire:click="$set('tab', 'single')" href="#" style="cursor:pointer;">
                <i class="fas fa-user mr-1"></i> Single
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'bulk' ? 'active' : '' }}"
               wire:click="$set('tab', 'bulk')" href="#" style="cursor:pointer;">
                <i class="fas fa-users mr-1"></i> Bulk
            </a>
        </li>
    </ul>

    {{-- Single Send --}}
    @if($tab === 'single')
        <form wire:submit="send">
            <div class="form-group">
                <label><i class="fas fa-mobile-alt mr-1"></i> Mobile Number</label>
                <input type="text" wire:model="mobile" inputmode="numeric"
                       class="form-control @error('mobile') is-invalid @enderror"
                       autocomplete="off" placeholder="e.g. 48123456">
                @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-id-badge mr-1"></i> Sender Id</label>
                <input type="text" wire:model="subject"
                       class="form-control @error('subject') is-invalid @enderror">
                @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-comment mr-1"></i> Message</label>
                <textarea wire:model="message" rows="4"
                          class="form-control @error('message') is-invalid @enderror"
                          placeholder="Type your message here…"></textarea>
                @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-success btn-block" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="send">
                    <i class="fas fa-paper-plane mr-1"></i> Send SMS
                </span>
                <span wire:loading wire:target="send">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Sending…
                </span>
            </button>
        </form>
    @endif

    {{-- Bulk Send --}}
    @if($tab === 'bulk')
        <form wire:submit="sendBulk">
            <div class="form-group">
                <label><i class="fas fa-id-badge mr-1"></i> Sender Id</label>
                <input type="text" wire:model="subject"
                       class="form-control @error('subject') is-invalid @enderror">
                @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-list mr-1"></i> Recipients
                    <small class="text-muted">(comma or newline separated, max 500)</small>
                </label>
                <textarea wire:model="recipients" rows="5"
                          class="form-control @error('recipients') is-invalid @enderror"
                          placeholder="92062344&#10;48123456&#10;99876543"></textarea>
                @error('recipients') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-file-csv mr-1"></i> Or Upload CSV
                    <small class="text-muted">(first column = phone numbers)</small>
                </label>
                <div class="custom-file">
                    <input type="file" wire:model="csvFile" accept=".csv,.txt"
                           class="custom-file-input @error('csvFile') is-invalid @enderror"
                           id="csvFile">
                    <label class="custom-file-label" for="csvFile">
                        {{ $csvFile ? $csvFile->getClientOriginalName() : 'Choose CSV file…' }}
                    </label>
                    @error('csvFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div wire:loading wire:target="csvFile" class="text-muted mt-1" style="font-size:0.85rem;">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Uploading…
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-comment mr-1"></i> Message</label>
                <textarea wire:model="message" rows="4"
                          class="form-control @error('message') is-invalid @enderror"
                          placeholder="Type your message here…"></textarea>
                @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-success btn-block" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="sendBulk">
                    <i class="fas fa-paper-plane mr-1"></i> Send Bulk SMS
                </span>
                <span wire:loading wire:target="sendBulk">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Sending… please wait
                </span>
            </button>
        </form>

        {{-- Bulk Results --}}
        @if(!empty($bulkResults))
            <hr>
            <h6 class="mt-3"><i class="fas fa-chart-bar mr-1"></i> Results</h6>

            <div class="row mb-3">
                <div class="col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ count(array_filter($bulkResults, fn($s) => $s === 'sent')) }}</h3>
                            <p>Sent</p>
                        </div>
                        <div class="icon"><i class="fas fa-check"></i></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ count(array_filter($bulkResults, fn($s) => $s === 'failed')) }}</h3>
                            <p>Failed</p>
                        </div>
                        <div class="icon"><i class="fas fa-times"></i></div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Phone Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bulkResults as $phone => $status)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $phone }}</td>
                                <td>
                                    @if($status === 'sent')
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Sent</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Failed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
