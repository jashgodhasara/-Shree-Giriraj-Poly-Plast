<?php

namespace Tests\Feature;

use App\Models\JobWorkClient;
use App\Models\JobWorkOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobWorkOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_job_work_order_with_automatic_calculation(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($user);

        $client = JobWorkClient::create([
            'name'         => 'Test Polymer Client',
            'company_name' => 'TPC Industries',
            'phone'        => '9876543210',
            'is_active'    => true,
        ]);

        $product = Product::create([
            'name'                => 'Product A',
            'sku'                 => 'PROD-A',
            'unit_type'           => 'PCS',
            'weight_per_piece'    => 10,
            'weight_unit'         => 'Gram',
            'weight_in_grams'     => 10,
            'job_work_applicable' => true,
            'wastage_percentage'  => 2,
            'price'               => 0.50,
            'gst_rate'            => 18,
            'is_active'           => true,
        ]);

        // Post Job Work: 500 KG material, 10g piece weight, 2% wastage, ₹0.50 rate
        $response = $this->post(route('jobworks.store'), [
            'client_id'          => $client->id,
            'job_work_number'    => 'JW-TEST-001',
            'order_date'         => now()->toDateString(),
            'status'             => 'Material Received',
            'rounding_method'    => 'floor',
            'additional_charges' => 0,
            'discount'           => 0,
            'tax'                => 0,
            'paid_amount'        => 0,
            'items' => [
                [
                    'product_id'           => $product->id,
                    'received_weight'      => 500,
                    'received_weight_unit' => 'KG',
                    'product_weight'       => 10,
                    'product_weight_unit'  => 'Gram',
                    'wastage_type'         => 'percentage',
                    'wastage_percentage'   => 2,
                    'rate_type'            => 'per_piece',
                    'rate'                 => 0.50,
                ]
            ]
        ]);

        $response->assertRedirect();

        $order = JobWorkOrder::where('job_work_number', 'JW-TEST-001')->with('items')->first();
        $this->assertNotNull($order);
        $this->assertEquals(500.0, (float) $order->total_received_weight_kg);
        $this->assertEquals(50000.0, (float) $order->total_gross_pieces); // 500,000 / 10
        $this->assertEquals(1000.0, (float) $order->total_wastage_pieces); // 2% of 50,000
        $this->assertEquals(49000.0, (float) $order->total_net_pieces); // 50,000 - 1,000
        $this->assertEquals(24500.0, (float) $order->grand_total); // 49,000 * 0.50

        $item = $order->items->first();
        $this->assertEquals(50000.0, (float) $item->gross_quantity);
        $this->assertEquals(1000.0, (float) $item->wastage_quantity);
        $this->assertEquals(49000.0, (float) $item->net_quantity);
        $this->assertEquals(24500.0, (float) $item->amount);
    }
}
