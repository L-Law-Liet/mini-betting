<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraudLog extends Model
{
    protected $guarded = [];
    protected $casts = [
        'details' => 'array',
    ];
}
