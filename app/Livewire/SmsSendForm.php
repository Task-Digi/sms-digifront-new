<?php

namespace App\Livewire;

use App\Services\SmsService;
use Livewire\Component;
use Livewire\WithFileUploads;

class SmsSendForm extends Component
{
    use WithFileUploads;

    // Single send
    public string $mobile = '';

    // Bulk send
    public string $recipients = '';
    public $csvFile = null;

    // Shared
    public string $subject = '';
    public string $message = '';
    public string $tab = 'single'; // 'single' | 'bulk'

    // Results
    public string $successMessage = '';
    public string $error = '';
    public array $bulkResults = [];

    public function mount(): void
    {
        $user = session('user');
        $this->subject = $user['sender_id'] ?? '';
    }

    public function send(SmsService $smsService): void
    {
        $this->successMessage = '';
        $this->error = '';

        $this->validate([
            'mobile'  => 'required|numeric|digits_between:8,10',
            'subject' => 'required|string|max:10',
            'message' => 'required|max:1000',
        ]);

        $user = session('user');

        $smsService->send($this->subject, $this->mobile, $this->message);
        $smsService->log([
            'user_id'   => $user['id'],
            'mobile_no' => $this->mobile,
            'sender_id' => $this->subject,
            'message'   => $this->message,
            'sms_count' => (int) ceil(strlen($this->message) / 140),
        ]);

        $this->mobile = '';
        $this->message = '';
        $this->successMessage = 'SMS Sent.';
    }

    public function sendBulk(SmsService $smsService): void
    {
        $this->successMessage = '';
        $this->error = '';
        $this->bulkResults = [];

        $this->validate([
            'subject' => 'required|string|max:10',
            'message' => 'required|max:1000',
            'csvFile' => 'nullable|file|mimes:csv,txt|max:2048',
        ]);

        $phones = [];

        // Parse numbers from the text input
        if (trim($this->recipients) !== '') {
            $phones = array_merge($phones, $smsService->parseNumbersFromString($this->recipients));
        }

        // Parse numbers from CSV
        if ($this->csvFile) {
            $phones = array_merge($phones, $smsService->parseNumbersFromCsv($this->csvFile->getRealPath()));
        }

        $phones = array_values(array_unique($phones));

        if (empty($phones)) {
            $this->error = 'No valid phone numbers found. Please enter numbers or upload a CSV.';
            return;
        }

        if (count($phones) > 500) {
            $this->error = 'Maximum 500 numbers allowed per batch.';
            return;
        }

        $user = session('user');
        $this->bulkResults = $smsService->sendBulk($this->subject, $phones, $this->message, $user['id']);

        $sent   = count(array_filter($this->bulkResults, fn($s) => $s === 'sent'));
        $failed = count($this->bulkResults) - $sent;

        $this->recipients = '';
        $this->csvFile    = null;
        $this->message    = '';
        $this->successMessage = "Bulk send complete: {$sent} sent, {$failed} failed.";
    }

    public function render()
    {
        return view('livewire.sms-send-form');
    }
}
