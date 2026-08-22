<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PlasticPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlasticPricingTest extends TestCase
{
    use RefreshDatabase;
    public function test_plastic_pricing_service_returns_rates()
    {
        $service = app(PlasticPricingService::class);
        $result = $service->getPrices(true);

        $this->assertEquals('success', $result['status']);
        $this->assertNotEmpty($result['items']);
        $this->assertArrayHasKey('material_name', $result['items'][0]);
        $this->assertArrayHasKey('current_price', $result['items'][0]);
    }

    public function test_api_plastic_prices_endpoint()
    {
        $response = $this->getJson('/api/plastic-prices');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'is_connected',
            'last_updated',
            'items' => [
                '*' => [
                    'material_name',
                    'category',
                    'current_price',
                    'currency',
                    'unit',
                ]
            ]
        ]);
    }

    public function test_materials_sync_artisan_command()
    {
        $this->artisan('materials:sync-api --force')->assertExitCode(0);
        $this->assertDatabaseHas('materials', [
            'name' => 'PP Homopolymer (Raffia Grade)',
            'unit' => 'Kg',
        ]);
    }
}
