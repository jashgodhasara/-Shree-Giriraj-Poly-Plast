<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DyeAndMould;
use App\Models\FactoryAsset;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DyeAndAssetInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_can_view_dyes_and_moulds_index(): void
    {
        DyeAndMould::create([
            'code'                   => 'DIE-TEST-01',
            'name'                   => 'Test 500ml Bottle Mould',
            'mould_type'             => 'Blow Mould',
            'cavities'               => 2,
            'ownership_type'         => 'Company Owned',
            'status'                 => 'Ready / In Storage',
            'total_shots_count'      => 1000,
            'service_interval_shots' => 50000,
        ]);

        $response = $this->actingAs($this->user)->get('/dyes');
        $response->assertStatus(200);
        $response->assertSee('Test 500ml Bottle Mould');
        $response->assertSee('DIE-TEST-01');
    }

    public function test_can_create_new_dye(): void
    {
        $customer = Customer::create(['name' => 'Acme Polymer Client', 'state' => 'Gujarat']);

        $response = $this->actingAs($this->user)->post('/dyes', [
            'name'                   => '28mm Fliptop Cap Mould',
            'mould_type'             => 'Injection Mould',
            'cavities'               => 8,
            'ownership_type'         => 'Client Owned',
            'customer_id'            => $customer->id,
            'rack_location'          => 'Rack B - Shelf 1',
            'status'                 => 'Ready / In Storage',
            'total_shots_count'      => 0,
            'service_interval_shots' => 50000,
            'purchase_cost'          => 0,
        ]);

        $response->assertRedirect('/dyes');
        $this->assertDatabaseHas('dyes_and_moulds', [
            'name'           => '28mm Fliptop Cap Mould',
            'cavities'       => 8,
            'ownership_type' => 'Client Owned',
            'customer_id'    => $customer->id,
        ]);
    }

    public function test_can_log_dye_maintenance(): void
    {
        $dye = DyeAndMould::create([
            'code'                   => 'DIE-MAINT-01',
            'name'                   => 'Mould for Maintenance',
            'mould_type'             => 'Injection Mould',
            'cavities'               => 4,
            'ownership_type'         => 'Company Owned',
            'status'                 => 'Under Maintenance',
            'total_shots_count'      => 52000,
            'service_interval_shots' => 50000,
        ]);

        $response = $this->actingAs($this->user)->post("/dyes/{$dye->id}/maintenance", [
            'maintenance_date' => Carbon::today()->toDateString(),
            'maintenance_type' => 'Preventive Cleaning & Pin Inspection',
            'shots_at_service' => 52000,
            'cost'             => 2500.00,
            'performed_by'     => 'In-house Tool Room',
            'work_description' => 'Ultrasonic bath cleaning and guide lubrication.',
            'status_after'     => 'Ready / In Storage',
            'next_due_date'    => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dye_maintenance_logs', [
            'dye_id'           => $dye->id,
            'maintenance_type' => 'Preventive Cleaning & Pin Inspection',
            'cost'             => 2500.00,
        ]);

        $dye->refresh();
        $this->assertEquals('Ready / In Storage', $dye->status);
    }

    public function test_can_view_and_create_factory_asset(): void
    {
        $supplier = Supplier::create(['name' => 'Windsor Machinery Supplier']);

        $response = $this->actingAs($this->user)->post('/factory-assets', [
            'name'                  => '150T Windsor Injection Machine',
            'category'              => 'Moulding Machine',
            'make_brand'            => 'Windsor Machines Ltd.',
            'model_number'          => 'Sprint-150',
            'tonnage_or_capacity'   => '150 Ton',
            'power_rating_kw'       => 28.5,
            'plant_location'        => 'Shop Floor Bay 1',
            'status'                => 'Operational',
            'supplier_id'           => $supplier->id,
            'purchase_cost'         => 1850000.00,
        ]);

        $response->assertRedirect('/factory-assets');
        $this->assertDatabaseHas('factory_assets', [
            'name'        => '150T Windsor Injection Machine',
            'category'    => 'Moulding Machine',
            'supplier_id' => $supplier->id,
        ]);
    }

    public function test_can_log_asset_maintenance(): void
    {
        $asset = FactoryAsset::create([
            'asset_code'          => 'MCH-TEST-01',
            'name'                => 'Auto Blow Moulding Unit',
            'category'            => 'Moulding Machine',
            'tonnage_or_capacity' => '5 Litre',
            'status'              => 'Breakdown',
        ]);

        $response = $this->actingAs($this->user)->post("/factory-assets/{$asset->id}/maintenance", [
            'service_date'         => Carbon::today()->toDateString(),
            'service_type'         => 'Hydraulic Valve Repair',
            'cost'                 => 4500.00,
            'technician_name'      => 'Rajesh Sharma',
            'problem_reported'     => 'Clamping cylinder pressure drop',
            'action_taken'         => 'Replaced hydraulic valve seal kit and tested',
            'status_after_service' => 'Operational',
            'next_service_due'     => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('asset_maintenance_logs', [
            'asset_id'     => $asset->id,
            'service_type' => 'Hydraulic Valve Repair',
            'cost'         => 4500.00,
        ]);

        $asset->refresh();
        $this->assertEquals('Operational', $asset->status);
    }

    public function test_dyes_and_assets_api_endpoints(): void
    {
        DyeAndMould::create([
            'code'           => 'DIE-API-01',
            'name'           => 'API Test Mould',
            'mould_type'     => 'Blow Mould',
            'cavities'       => 1,
            'ownership_type' => 'Company Owned',
            'status'         => 'Ready / In Storage',
        ]);

        FactoryAsset::create([
            'asset_code' => 'MCH-API-01',
            'name'       => 'API Test Chiller',
            'category'   => 'Compressor & Chiller',
            'status'     => 'Operational',
        ]);

        $token = $this->user->createToken('test-token')->plainTextToken;

        $responseDyes = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/dyes');
        $responseDyes->assertStatus(200);
        $responseDyes->assertJsonFragment(['name' => 'API Test Mould']);

        $responseAssets = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/factory-assets');
        $responseAssets->assertStatus(200);
        $responseAssets->assertJsonFragment(['name' => 'API Test Chiller']);
    }
}
