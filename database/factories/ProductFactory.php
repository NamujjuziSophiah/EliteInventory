<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-###')),
            'barcode' => $this->faker->unique()->ean13(),
            'category_id' => null,
            'supplier_id' => null,
            'description' => $this->faker->optional()->sentence(),
            'cost_price' => $this->faker->randomFloat(2, 0.5, 100),
            'selling_price' => $this->faker->randomFloat(2, 1, 200),
            'stock' => $this->faker->numberBetween(0, 100),
            'image_path' => null,
            'is_active' => true,
        ];
    }
}
