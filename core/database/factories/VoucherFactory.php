<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Variation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'variation_id' => Variation::inRandomOrder()->first()->id,
            'order_id'     => Order::inRandomOrder()->first()->id,
            'code'         => strRandom(),
        ];
    }
}
