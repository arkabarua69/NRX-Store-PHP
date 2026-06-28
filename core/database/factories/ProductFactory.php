<?php

namespace Database\Factories;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'   => fake()->word(),
            'slug'    => fake()->slug(),
            'content' => fake()->sentence(),
            'type'    => Status::TOPUP,
        ];
    }
}
