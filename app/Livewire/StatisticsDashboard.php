<?php

namespace App\Livewire;

use App\Models\SmsLog;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class StatisticsDashboard extends Component
{
    public function render()
    {
        $totalSms     = SmsLog::count();
        $totalUsers   = User::count();
        $smsToday     = SmsLog::whereDate('created_at', Carbon::today())->count();
        $smsThisMonth = SmsLog::whereYear('created_at', Carbon::now()->year)
                              ->whereMonth('created_at', Carbon::now()->month)
                              ->count();

        $rawDaily = SmsLog::selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
            ->where('created_at', '>=', Carbon::now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->pluck('cnt', 'day');

        $daily = collect();
        for ($i = 29; $i >= 0; $i--) {
            $key = Carbon::now()->subDays($i)->format('Y-m-d');
            $daily[$key] = (int) ($rawDaily[$key] ?? 0);
        }

        $maxDaily = max(1, $daily->max());

        $perUser = User::leftJoin('sms_logs', 'users.id', '=', 'sms_logs.user_id')
            ->selectRaw('users.id, users.name, users.sender_id, users.is_admin, COUNT(sms_logs.id) as sms_count, COALESCE(SUM(sms_logs.sms_count), 0) as segments')
            ->groupBy('users.id', 'users.name', 'users.sender_id', 'users.is_admin')
            ->orderByDesc('sms_count')
            ->get();

        return view('livewire.statistics-dashboard', compact(
            'totalSms', 'totalUsers', 'smsToday', 'smsThisMonth', 'daily', 'maxDaily', 'perUser'
        ));
    }
}
