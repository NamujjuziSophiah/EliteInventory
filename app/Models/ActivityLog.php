<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $guarded = [];
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}
