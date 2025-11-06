<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'total' => 0,
            'payment_type' => $this->faker->randomElement(['cash','card','mobile']),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Sale $sale) {
            // Create 1-3 sale items and adjust sale total
            $items = SaleItem::factory()->count(rand(1, 3))->create(['sale_id' => $sale->id]);
            $total = $items->reduce(fn($carry, $item) => $carry + $item->price * $item->qty, 0);
            $sale->update(['total' => $total]);
        });
    }
}
