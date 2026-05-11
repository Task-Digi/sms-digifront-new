<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'mobile', 'sender_id', 'is_active', 'is_admin'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_admin'  => 'boolean',
    ];
}
