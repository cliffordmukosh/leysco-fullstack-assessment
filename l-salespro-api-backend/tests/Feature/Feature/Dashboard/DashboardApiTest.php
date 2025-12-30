<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $user = User::where('email', 'david.kariuki@leysco.co.ke')->firstOrFail();
        $this->token = $user->createToken('dashboard-tests')->plainTextToken;
    }

    /** @test */
    public function can_get_dashboard_summary()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/v1/dashboard/summary?period=month');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Dashboard summary retrieved successfully',
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_sales',
                    'order_count',
                    'average_order_value',
                    'inventory_turnover_rate',
                    'period',
                ],
                'message',
            ]);
    }

    /** @test */
    public function can_get_sales_performance()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/v1/dashboard/sales-performance?period=week');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Sales performance data retrieved',
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['date', 'total'],
                ],
                'message',
            ]);
    }

    /** @test */
    public function can_get_inventory_status()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/v1/dashboard/inventory-status');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Inventory status by category retrieved',
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['category_name', 'total_products', 'total_stock'],
                ],
                'message',
            ]);
    }

    /** @test */
    public function can_get_top_products()
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/v1/dashboard/top-products?period=month');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Top selling products retrieved',
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'product_id', 'sku', 'name', 'category',
                        'total_quantity_sold', 'total_revenue', 'average_unit_price',
                    ],
                ],
                'message',
            ]);
    }

    /** @test */
    public function endpoints_require_authentication()
    {
        $endpoints = [
            '/api/v1/dashboard/summary',
            '/api/v1/dashboard/sales-performance',
            '/api/v1/dashboard/inventory-status',
            '/api/v1/dashboard/top-products',
        ];

        foreach ($endpoints as $url) {
            $this->getJson($url)
                 ->assertUnauthorized()
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('message', fn($msg) => str_contains($msg, 'Authentication required'));
        }
    }
}