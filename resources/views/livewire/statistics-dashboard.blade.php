<div>
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalSms) }}</h3>
                    <p>Total SMS Sent</p>
                </div>
                <div class="icon"><i class="fas fa-paper-plane"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($smsToday) }}</h3>
                    <p>SMS Today</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($smsThisMonth) }}</h3>
                    <p>SMS This Month</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($totalUsers) }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> SMS sent &mdash; last 30 days</h3>
                </div>
                <div class="card-body">
                    <div style="display:flex; align-items:flex-end; gap:4px; height:180px; padding-bottom:8px; border-bottom:1px solid #ddd;">
                        @foreach ($daily as $day => $count)
                            <div title="{{ $day }}: {{ $count }} SMS"
                                 style="flex:1; min-width:8px; background:{{ $count > 0 ? '#007bff' : '#e9ecef' }}; height:{{ $count > 0 ? max(2, round($count / $maxDaily * 100)) : 2 }}%; border-radius:2px 2px 0 0;">
                            </div>
                        @endforeach
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:11px; color:#888; margin-top:6px;">
                        <span>{{ $daily->keys()->first() }}</span>
                        <span>Today</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users mr-1"></i> SMS by user</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>User</th>
                                <th>Sender ID</th>
                                <th>Role</th>
                                <th class="text-right">Messages</th>
                                <th class="text-right">Segments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perUser as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td><span class="badge badge-secondary">{{ $row->sender_id }}</span></td>
                                    <td>
                                        @if ($row->is_admin)
                                            <span class="badge badge-danger">Admin</span>
                                        @else
                                            <span class="badge badge-info">User</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($row->sms_count) }}</td>
                                    <td class="text-right">{{ number_format($row->segments) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No data yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
