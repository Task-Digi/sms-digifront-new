<?php

namespace App\Services;

use App\Models\SmsLog;
use GuzzleHttp\Client;

class SmsService
{
    public function send(string $senderId, string $phone, string $message): void
    {
        $base = env('SMS_GATEWAY_URL').'?key='.env('SMS_GATEWAY_KEY');
        $url  = $base.'&avs='.$senderId.'&dest='.$this->formatPhone($phone).'&msg='.$message;
        $client = new Client();
        $client->request('GET', $url);
    }

    /**
     * Ensure phone number has Norway country code (47) prefix.
     * Strips leading 0, +, or existing 47 prefix before adding it.
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone); // strip non-digits
        $phone       = ltrim($phone, '0');           // remove leading 0
        $countryCode = env('SMS_COUNTRY_CODE', '47');

        if (str_starts_with($phone, $countryCode) && strlen($phone) === strlen($countryCode) + 8) {
            return $phone; // already has country code
        }

        return $countryCode . $phone;
    }

    public function sendBulk(string $senderId, array $phones, string $message, int $userId): array
    {
        $results  = [];
        $smsCount = (int) ceil(strlen($message) / 140);

        // Format all numbers and build comma-separated dest
        $formatted = array_map(fn($p) => $this->formatPhone($p), $phones);
        $dest      = implode(',', $formatted);

        try {
            $base = env('SMS_GATEWAY_URL').'?key='.env('SMS_GATEWAY_KEY');
            $url  = $base.'&avs='.urlencode($senderId).'&dest='.urlencode($dest).'&msg='.urlencode($message);
            $client = new Client();
            $client->request('GET', $url);

            // Log all numbers and mark as sent
            foreach ($phones as $phone) {
                $this->log([
                    'user_id'   => $userId,
                    'mobile_no' => $phone,
                    'sender_id' => $senderId,
                    'message'   => $message,
                    'sms_count' => $smsCount,
                ]);
                $results[$phone] = 'sent';
            }
        } catch (\Exception $e) {
            foreach ($phones as $phone) {
                $results[$phone] = 'failed';
            }
        }

        return $results;
    }

    public function log(array $data): void
    {
        SmsLog::create($data);
    }

    /**
     * Parse phone numbers from a raw string (comma, newline, or space separated).
     */
    public function parseNumbersFromString(string $input): array
    {
        $numbers = preg_split('/[\s,;]+/', $input, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_filter($numbers)));
    }

    /**
     * Parse phone numbers from a CSV file path (first column, skip header if non-numeric).
     */
    public function parseNumbersFromCsv(string $filePath): array
    {
        $numbers = [];
        if (($handle = fopen($filePath, 'r')) === false) {
            return [];
        }
        while (($row = fgetcsv($handle)) !== false) {
            $value = trim($row[0] ?? '');
            if ($value !== '' && ctype_digit($value)) {
                $numbers[] = $value;
            }
        }
        fclose($handle);
        return array_values(array_unique($numbers));
    }
}
