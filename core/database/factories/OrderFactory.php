<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\Variation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::inRandomOrder()->first()->id,
            'product_id'   => Product::inRandomOrder()->first()->id,
            'variation_id' => Variation::inRandomOrder()->first()->id,
            'amount'       => fake()->numberBetween(10, 100),
            'track_id'     => strRandom(),
        ];
    }
}
