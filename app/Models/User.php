<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'mobile', 'sender_id', 'is_active', 'is_admin', 'monthly_sms_limit'];

    protected $casts = [
        'is_active'         => 'boolean',
        'is_admin'          => 'boolean',
        'monthly_sms_limit' => 'integer',
    ];

    public function smsUsageThisMonth(): int
    {
        return (int) SmsLog::where('user_id', $this->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('sms_count');
    }

    public function smsRemainingThisMonth(): ?int
    {
        if ($this->monthly_sms_limit === null) {
            return null;
        }
        return max(0, $this->monthly_sms_limit - $this->smsUsageThisMonth());
    }
}
