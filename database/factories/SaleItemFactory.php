<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SaleItem;
use App\Models\Product;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition()
    {
        return [
            // create a product for this sale item when not provided
            'product_id' => Product::factory(),
            'qty' => $this->faker->numberBetween(1, 5),
            'price' => $this->faker->randomFloat(2, 1, 200),
            'cost_per_unit' => $this->faker->randomFloat(2, 0.5, 150),
        ];
    }
}
