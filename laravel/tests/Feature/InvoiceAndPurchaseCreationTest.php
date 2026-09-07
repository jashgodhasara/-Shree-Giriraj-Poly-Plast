<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Material;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceAndPurchaseCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Supplier $supplier;
    protected Product $product;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);

        $this->customer = Customer::create([
            'name'     => 'Ahmedabad Plastics Corp',
            'phone'    => '9876543210',
            'state'    => 'Gujarat',
            'country'  => 'India',
            'gstin'    => '24AABCS1429B1Z1',
        ]);

        $this->supplier = Supplier::create([
            'name'     => 'Reliance Polymers Ltd',
            'phone'    => '9123456780',
            'state'    => 'Gujarat',
            'country'  => 'India',
            'gstin'    => '24AAACR5055K1ZI',
        ]);

        $this->product = Product::create([
            'name'           => '1000ml HDPE Bottle',
            'sku'            => 'BTL-1000-TEST',
            'unit'           => 'PCS',
            'price'          => 20.00,
            'gst_rate'       => 18.00,
            'stock_quantity' => 0.00, // Zero initial stock
        ]);

        $this->material = Material::create([
            'name'           => 'HDPE Granules B56003',
            'type'           => 'Raw Material',
            'unit'           => 'KG',
            'stock_quantity' => 500.00,
            'price_per_unit' => 95.00,
        ]);
    }

    public function test_can_create_sales_invoice_even_with_zero_initial_stock()
    {
        $payload = [
            'customer_id'  => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'notes'        => 'Urgent factory delivery',
            'items'        => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 50,
                    'unit_price' => 22.50,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('invoices.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $this->customer->id,
            'subtotal'    => 1125.00,
        ]);

        $this->assertDatabaseHas('ledgers', [
            'entity_type' => 'Customer',
            'entity_id'   => $this->customer->id,
            'type'        => 'Debit',
        ]);
    }

    public function test_can_create_purchase_order_with_auto_receive()
    {
        $payload = [
            'supplier_id'   => $this->supplier->id,
            'po_date'       => now()->toDateString(),
            'payment_terms' => '30 Days Credit',
            'auto_receive'  => true,
            'items'         => [
                [
                    'material_id' => $this->material->id,
                    'hsn_code'    => '3901',
                    'quantity'    => 200,
                    'unit_price'  => 95.00,
                    'gst_rate'    => 18,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('purchase-orders.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'status'      => 'Received',
            'subtotal'    => 19000.00,
        ]);

        $this->material->refresh();
        $this->assertEquals(700.00, (float) $this->material->stock_quantity);

        $this->assertDatabaseHas('ledgers', [
            'entity_type' => 'Supplier',
            'entity_id'   => $this->supplier->id,
            'type'        => 'Credit',
        ]);
    }
}
