<?php

namespace App\Livewire;

use App\Models\SmsLog;
use Livewire\Component;
use Livewire\WithPagination;

class SmsTracking extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        return view('livewire.sms-tracking', [
            'tracking' => SmsLog::latest()->paginate(50),
        ]);
    }
}
