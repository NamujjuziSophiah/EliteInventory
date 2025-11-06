<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseItem;
use App\Models\Supplier;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Purchase has many items
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Optionally the supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
