<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $guarded = [];
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * The user responsible for this activity (if any).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
