<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use App\Models\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ResellerApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $reseller;
    private Product $product;
    private Variation $variation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reseller = User::factory()->create([
            'is_reseller' => true,
            'balance' => 1000,
        ]);

        $this->product = Product::factory()->create(['status' => 1]);
        $this->variation = Variation::factory()->create([
            'product_id' => $this->product->id,
            'price' => 100,
            'stock' => 10,
        ]);
    }

    public function test_reseller_can_view_products(): void
    {
        $response = $this->actingAs($this->reseller)
            ->getJson('/api/reseller/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'title', 'slug', 'type', 'variations'],
                ],
            ]);
    }

    public function test_reseller_can_place_order(): void
    {
        $response = $this->actingAs($this->reseller)
            ->postJson('/api/reseller/order/place', [
                'variation_id' => $this->variation->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->reseller->refresh();
        $this->assertEquals(900, $this->reseller->balance);
    }

    public function test_reseller_cannot_order_without_balance(): void
    {
        $poorReseller = User::factory()->create([
            'is_reseller' => true,
            'balance' => 10,
        ]);

        $response = $this->actingAs($poorReseller)
            ->postJson('/api/reseller/order/place', [
                'variation_id' => $this->variation->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Insufficient balance.',
            ]);
    }

    public function test_reseller_can_check_balance(): void
    {
        $response = $this->actingAs($this->reseller)
            ->getJson('/api/reseller/balance');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => ['balance' => 1000],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_api(): void
    {
        $response = $this->getJson('/api/reseller/products');
        $response->assertStatus(401);

        $response = $this->postJson('/api/reseller/order/place', []);
        $response->assertStatus(401);
    }
}
