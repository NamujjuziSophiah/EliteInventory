<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerCredit extends Model
{
    use HasFactory;

    protected $table = 'customer_credits';

    protected $fillable = ['customer_id','sale_id','amount','due_date','paid','created_by'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
