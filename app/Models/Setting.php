<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['name', 'logo', 'currency', 'auto_redirect', 'default_markup_percent', 'sku_prefix', 'sku_padding', 'sku_next'];
    protected $casts = [
        'auto_redirect' => 'boolean',
        'default_markup_percent' => 'float',
        'sku_padding' => 'integer',
        'sku_next' => 'integer',
    ];
}
