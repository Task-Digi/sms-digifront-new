<?php

namespace App\Livewire;

use App\Models\SmsLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SmsTracking extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $sessionUser = session('user') ?? [];
        $query = SmsLog::latest();

        if (!($sessionUser['is_admin'] ?? false)) {
            $senderId = $sessionUser['sender_id'] ?? null;
            if ($senderId) {
                $query->where('sender_id', $senderId);
            } else {
                $query->where('user_id', $sessionUser['id'] ?? 0);
            }
        }

        $tracking = $query->paginate(50);
        $users = User::whereIn('id', $tracking->pluck('user_id')->unique())->pluck('name', 'id');

        return view('livewire.sms-tracking', compact('tracking', 'users'));
    }
}
