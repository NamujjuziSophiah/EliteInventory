<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name','sku','barcode','category_id','supplier_id','description','cost_price','selling_price','stock','image_path','is_active','markup_percent','unit'];

    protected $casts = [
        'is_active' => 'boolean',
        'markup_percent' => 'float',
        'cost_price' => 'float',
        'selling_price' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Purchase items referencing this product
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
