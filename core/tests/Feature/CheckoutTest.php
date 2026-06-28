<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Variation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $product;
    private $variation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->product = $this->createProduct();
        $this->variation = $this->createVariation();
    }

    private function createUser()
    {
        return User::factory()->create();
    }

    private function createProduct()
    {
        return Product::factory()->create();
    }

    private function createVariation()
    {
        return Variation::factory()->create();
    }


    public function test_topup_checkout(): void
    {
        $data = [
            'variation_id'   => 1,
            'quantity'       => 1,
            'payment_method' => 'UddoktaPay',
        ];

        $response = $this->actingAs($this->user)->post(route('user.topup.buynow'), $data);

        $response->assertStatus(302)
            ->assertRedirectContains('https://sandbox.uddoktapay.com');

        $this->assertDatabaseHas('orders', [
            'variation_id' => $data['variation_id']
        ]);

        $last = Variation::latest()->first();

        $this->assertEquals($data['variation_id'], $last->id);

    }
}