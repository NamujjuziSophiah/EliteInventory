<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    protected function log($action, Product $product, $changes = null)
    {
        try {
            $user = Auth::user();
            ActivityLog::create([
                // Use optional()/data_get to avoid property access on null
                'user_id' => optional($user)->id ?? data_get($user, 'id'),
                'role' => optional($user)->role ?? data_get($user, 'role'),
                'auditable_type' => Product::class,
                // product should be a model, but guard anyway
                'auditable_id' => optional($product)->id ?? data_get($product, 'id'),
                'action' => $action,
                'old_values' => data_get($changes, 'old'),
                'new_values' => data_get($changes, 'new'),
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
            ]);
        } catch (\Exception $e) {
            // do not interrupt flow on logging failure
        }
    }

    public function created(Product $product)
    {
        $this->log('created', $product, ['new' => $product->toArray()]);
    }

    public function updated(Product $product)
    {
        $this->log('updated', $product, ['old' => $product->getOriginal(), 'new' => $product->getAttributes()]);
    }

    public function deleted(Product $product)
    {
        $this->log('deleted', $product, ['old' => $product->toArray()]);
    }
}
