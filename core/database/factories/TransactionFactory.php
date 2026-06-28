<?php

namespace Database\Factories;

use App\Models\Deposit;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::inRandomOrder()->first()->id,
            'order_id'       => Order::inRandomOrder()->first()->id,
            'deposit_id'     => Deposit::inRandomOrder()->first()->id,
            'amount'         => fake()->numberBetween(10, 100),
            'payment_method' => 'UddoktaPay',
            'transaction_id' => strRandom(),
            'remarks'        => fake()->word(),
        ];
    }
}
