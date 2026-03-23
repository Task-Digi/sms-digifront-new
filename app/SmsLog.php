<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $table = 'sms_logs';
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $guarded = [];
}
