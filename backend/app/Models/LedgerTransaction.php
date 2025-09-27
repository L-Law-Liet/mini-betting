<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];
}
