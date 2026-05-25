<?php

namespace App\Livewire;

use App\Models\Template;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Collection;
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

    // Templates
    public ?int $selectedTemplateId = null;

    // User flags
    public bool $isAdmin = false;
    public string $defaultSenderId = '';

    // Results
    public string $successMessage = '';
    public string $error = '';
    public array $bulkResults = [];

    public function mount(): void
    {
        $user = session('user');
        $this->defaultSenderId = $user['sender_id'] ?? '';
        $this->subject = $this->defaultSenderId;
        $this->isAdmin = (bool) ($user['is_admin'] ?? false);

        $this->mobile  = (string) request()->query('mobile', request()->query('recipient', ''));
        $this->message = (string) request()->query('message', request()->query('text', ''));

        $slug = (string) request()->query('template', '');
        if ($slug !== '') {
            $template = $this->templatesQuery()->where('slug', $slug)->first();
            if ($template) {
                $this->message = $this->appendBody($this->message, $template->body);
                if ($template->sender_id) {
                    $this->subject = $template->sender_id;
                }
                $this->selectedTemplateId = $template->id;
            }
        }

        $this->message = $this->applyPlaceholders($this->message);
    }

    private function applyPlaceholders(string $text): string
    {
        if ($text === '' || !str_contains($text, '[')) {
            return $text;
        }

        $reserved = ['mobile', 'recipient', 'message', 'text', 'template'];

        return preg_replace_callback('/\[([\p{L}\p{N}_]+)\]/u', function ($m) use ($reserved) {
            $key = $m[1];
            if (in_array(strtolower($key), $reserved, true)) {
                return $m[0];
            }
            $value = request()->query($key);
            return ($value === null || $value === '') ? $m[0] : (string) $value;
        }, $text);
    }

    public function updatedSelectedTemplateId($value): void
    {
        if (!$value) {
            return;
        }

        $template = $this->templatesQuery()->find($value);
        if (!$template) {
            return;
        }

        $this->message = $this->appendBody($this->message, $template->body);

        if ($template->sender_id) {
            $this->subject = $template->sender_id;
        } elseif (!$this->isAdmin) {
            $this->subject = $this->defaultSenderId;
        }

        $this->selectedTemplateId = null;
    }

    private function appendBody(string $current, string $body): string
    {
        $current = rtrim($current);
        return $current === '' ? $body : $current . "\n" . $body;
    }

    private function resolveLockedSenderId(): string
    {
        $allowed = $this->templatesQuery()
            ->whereNotNull('sender_id')
            ->pluck('sender_id')
            ->push($this->defaultSenderId)
            ->unique()
            ->all();

        return in_array($this->subject, $allowed, true)
            ? $this->subject
            : $this->defaultSenderId;
    }

    private function templatesQuery()
    {
        $senderId = session('user')['sender_id'] ?? null;
        return Template::query()
            ->where(function ($q) use ($senderId) {
                $q->whereNull('sender_id');
                if ($senderId) {
                    $q->orWhere('sender_id', $senderId);
                }
            })
            ->orderBy('name');
    }

    public function send(SmsService $smsService): void
    {
        $this->successMessage = '';
        $this->error = '';

        if (!$this->isAdmin) {
            $this->subject = $this->resolveLockedSenderId();
        }

        $this->validate([
            'mobile'  => 'required|numeric|digits_between:8,10',
            'subject' => 'required|string|max:10',
            'message' => 'required|max:1000',
        ]);

        $sessionUser = session('user');
        $segmentsPerMsg = (int) ceil(strlen($this->message) / 140);

        if (!$this->ensureQuota($sessionUser['id'], $segmentsPerMsg)) {
            return;
        }

        $smsService->send($this->subject, $this->mobile, $this->message);
        $smsService->log([
            'user_id'   => $sessionUser['id'],
            'mobile_no' => $this->mobile,
            'sender_id' => $this->subject,
            'message'   => $this->message,
            'sms_count' => $segmentsPerMsg,
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

        if (!$this->isAdmin) {
            $this->subject = $this->resolveLockedSenderId();
        }

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

        $sessionUser = session('user');
        $segmentsPerMsg = (int) ceil(strlen($this->message) / 140);
        $plannedSegments = count($phones) * $segmentsPerMsg;

        if (!$this->ensureQuota($sessionUser['id'], $plannedSegments)) {
            return;
        }

        $this->bulkResults = $smsService->sendBulk($this->subject, $phones, $this->message, $sessionUser['id']);

        $sent   = count(array_filter($this->bulkResults, fn($s) => $s === 'sent'));
        $failed = count($this->bulkResults) - $sent;

        $this->recipients = '';
        $this->csvFile    = null;
        $this->message    = '';
        $this->successMessage = "Bulk send complete: {$sent} sent, {$failed} failed.";
    }

    /**
     * Verify the user has enough monthly quota for the planned send.
     * Returns false (and sets $this->error) when the send would exceed the cap.
     */
    private function ensureQuota(int $userId, int $plannedSegments): bool
    {
        $user = User::find($userId);
        if (!$user || $user->monthly_sms_limit === null) {
            return true; // unlimited
        }

        $used = $user->smsUsageThisMonth();
        $remaining = max(0, $user->monthly_sms_limit - $used);

        if ($plannedSegments > $remaining) {
            $this->error = "Monthly SMS limit reached. Used {$used} of {$user->monthly_sms_limit} segments this month — this send needs {$plannedSegments}. Contact an admin to raise the limit.";
            return false;
        }

        return true;
    }

    public function getQuotaStatusProperty(): array
    {
        $sessionUser = session('user');
        $user = $sessionUser ? User::find($sessionUser['id']) : null;

        if (!$user || $user->monthly_sms_limit === null) {
            return ['limit' => null, 'used' => $user ? $user->smsUsageThisMonth() : 0, 'remaining' => null];
        }

        $used = $user->smsUsageThisMonth();
        return [
            'limit'     => $user->monthly_sms_limit,
            'used'      => $used,
            'remaining' => max(0, $user->monthly_sms_limit - $used),
        ];
    }

    public function render()
    {
        return view('livewire.sms-send-form', [
            'templates' => $this->templatesQuery()->get(['id', 'name', 'sender_id']),
        ]);
    }
}
