<div>
    <table class="table table-bordered table-striped table-hover mb-0">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th><i class="fas fa-mobile-alt mr-1"></i> Mobile</th>
                <th><i class="fas fa-id-badge mr-1"></i> Sender Id</th>
                <th><i class="fas fa-comment mr-1"></i> Message</th>
                <th><i class="fas fa-user mr-1"></i> User</th>
                <th><i class="fas fa-clock mr-1"></i> Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tracking as $track)
                <tr>
                    <td>{{ $tracking->firstItem() + $loop->index }}</td>
                    <td>{{ $track->mobile_no }}</td>
                    <td><span class="badge badge-primary">{{ $track->sender_id }}</span></td>
                    <td>{{ $track->message }}</td>
                    <td>{{ config('settings.users_id')[$track->user_id]['name'] ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($track->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i> No messages sent yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-3 py-2">
        {{ $tracking->links() }}
    </div>
</div>
