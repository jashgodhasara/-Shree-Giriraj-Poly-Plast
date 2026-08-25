<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Services\GstTaxCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GstTaxAutoDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'admin@shreegiriraj.com',
            'role'  => 'admin'
        ]);
    }

    public function test_gst_state_lookup_from_gstin(): void
    {
        $this->assertEquals('Gujarat', GstTaxCalculationService::getStateFromGstin('24ABCDE1234F1Z5'));
        $this->assertEquals('Maharashtra', GstTaxCalculationService::getStateFromGstin('27ABCDE1234F1Z5'));
        $this->assertEquals('Rajasthan', GstTaxCalculationService::getStateFromGstin('08ABCDE1234F1Z5'));
        $this->assertEquals('Delhi', GstTaxCalculationService::getStateFromGstin('07ABCDE1234F1Z5'));
        $this->assertEquals('Karnataka', GstTaxCalculationService::getStateFromGstin('29ABCDE1234F1Z5'));
    }

    public function test_determine_tax_regime_for_gujarat_intra_state(): void
    {
        $regime = GstTaxCalculationService::determineTaxRegime('India', 'Gujarat', '24ABCDE1234F1Z5');
        $this->assertEquals('INTRA_STATE', $regime['type']);
        $this->assertEquals(0.5, $regime['cgst_split']);
        $this->assertEquals(0.5, $regime['sgst_split']);
        $this->assertEquals(0.0, $regime['igst_split']);
        $this->assertFalse($regime['is_export']);
    }

    public function test_determine_tax_regime_for_inter_state(): void
    {
        $regime = GstTaxCalculationService::determineTaxRegime('India', 'Maharashtra', '27ABCDE1234F1Z5');
        $this->assertEquals('INTER_STATE', $regime['type']);
        $this->assertEquals(0.0, $regime['cgst_split']);
        $this->assertEquals(0.0, $regime['sgst_split']);
        $this->assertEquals(1.0, $regime['igst_split']);
        $this->assertTrue($regime['is_interstate']);
    }

    public function test_determine_tax_regime_for_export_overseas(): void
    {
        $regime = GstTaxCalculationService::determineTaxRegime('United States', 'California', null, 'Export with LUT');
        $this->assertEquals('EXPORT_LUT', $regime['type']);
        $this->assertEquals(0.0, $regime['cgst_split']);
        $this->assertEquals(0.0, $regime['sgst_split']);
        $this->assertEquals(0.0, $regime['igst_split']);
        $this->assertTrue($regime['is_export']);
    }

    public function test_invoice_creation_with_gujarat_customer_applies_cgst_and_sgst(): void
    {
        $customer = Customer::create([
            'name'     => 'Ahmedabad Plastics',
            'state'    => 'Gujarat',
            'country'  => 'India',
            'gstin'    => '24AAACH7409R1ZZ',
        ]);

        $product = Product::create([
            'name'           => '500ml HDPE Bottle',
            'price'          => 10.00,
            'gst_rate'       => 18.00,
            'hsn_code'       => '3923',
            'stock_quantity' => 1000,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('invoices.store'), [
            'customer_id'  => $customer->id,
            'invoice_date' => now()->toDateString(),
            'items'        => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 100, // Subtotal = 1000
                    'unit_price' => 10.00,
                ]
            ]
        ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'subtotal'    => 1000.00,
            'cgst'        => 90.00,  // 9%
            'sgst'        => 90.00,  // 9%
            'igst'        => 0.00,
            'grand_total' => 1180.00,
        ]);
    }

    public function test_invoice_creation_with_out_of_state_customer_applies_igst(): void
    {
        $customer = Customer::create([
            'name'     => 'Mumbai Packaging Corp',
            'state'    => 'Maharashtra',
            'country'  => 'India',
            'gstin'    => '27AAACH7409R1ZZ',
        ]);

        $product = Product::create([
            'name'           => '1000ml Jar',
            'price'          => 20.00,
            'gst_rate'       => 18.00,
            'hsn_code'       => '3923',
            'stock_quantity' => 500,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('invoices.store'), [
            'customer_id'  => $customer->id,
            'invoice_date' => now()->toDateString(),
            'items'        => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 50, // Subtotal = 1000
                    'unit_price' => 20.00,
                ]
            ]
        ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'subtotal'    => 1000.00,
            'cgst'        => 0.00,
            'sgst'        => 0.00,
            'igst'        => 180.00, // 18% full IGST
            'grand_total' => 1180.00,
        ]);
    }
}
