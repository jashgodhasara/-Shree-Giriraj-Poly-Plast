<?php

namespace Tests\Unit;

use App\Services\JobWorkCalculationService;
use PHPUnit\Framework\TestCase;

class JobWorkCalculationServiceTest extends TestCase
{
    private JobWorkCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new JobWorkCalculationService();
    }

    /**
     * Test 1: 500 KG / 10 Gram = 50,000 PCS
     */
    public function test_500kg_with_10g_piece_produces_50000_gross_pieces(): void
    {
        $receivedGrams = $this->service->convertToGrams(500, 'KG');
        $this->assertEquals(500000, $receivedGrams);

        $grossQty = $this->service->calculateGrossQuantity($receivedGrams, 10, 'floor');
        $this->assertEquals(50000, $grossQty);
    }

    /**
     * Test 2: 100 KG / 20 Gram = 5,000 PCS
     */
    public function test_100kg_with_20g_piece_produces_5000_gross_pieces(): void
    {
        $receivedGrams = $this->service->convertToGrams(100, 'KG');
        $grossQty = $this->service->calculateGrossQuantity($receivedGrams, 20, 'floor');
        $this->assertEquals(5000, $grossQty);
    }

    /**
     * Test 3: 500 KG / 10 Gram with 2% wastage and ₹0.50 rate
     * Gross: 50,000 | Wastage: 1,000 | Net: 49,000 | Amount: ₹24,500
     */
    public function test_full_job_work_item_calculation(): void
    {
        $item = $this->service->calculateItem([
            'received_weight'      => 500,
            'received_weight_unit' => 'KG',
            'product_weight'       => 10,
            'product_weight_unit'  => 'Gram',
            'wastage_type'         => 'percentage',
            'wastage_percentage'   => 2,
            'rate_type'            => 'per_piece',
            'rate'                 => 0.50,
        ], 'floor');

        $this->assertEquals(500000, $item['received_weight_grams']);
        $this->assertEquals(10, $item['product_weight_grams']);
        $this->assertEquals(50000, $item['gross_quantity']);
        $this->assertEquals(1000, $item['wastage_quantity']);
        $this->assertEquals(49000, $item['net_quantity']);
        $this->assertEquals(24500, $item['amount']);
    }

    /**
     * Test 4: 1 KG / 30 Gram with different rounding methods
     */
    public function test_decimal_rounding_modes(): void
    {
        $receivedGrams = $this->service->convertToGrams(1, 'KG'); // 1000g
        $productGrams  = 30; // 30g -> 33.3333...

        $floorResult   = $this->service->calculateGrossQuantity($receivedGrams, $productGrams, 'floor');
        $roundResult   = $this->service->calculateGrossQuantity($receivedGrams, $productGrams, 'round');
        $ceilResult    = $this->service->calculateGrossQuantity($receivedGrams, $productGrams, 'ceil');
        $decimalResult = $this->service->calculateGrossQuantity($receivedGrams, $productGrams, 'decimal');

        $this->assertEquals(33, $floorResult);
        $this->assertEquals(33, $roundResult);
        $this->assertEquals(34, $ceilResult);
        $this->assertEquals(33.3333, $decimalResult);
    }

    /**
     * Test 5: Rate calculations: Per Piece, Per KG, and Fixed
     */
    public function test_rate_types(): void
    {
        // Per Piece: 49,000 PCS @ ₹0.50 = ₹24,500
        $perPieceAmount = $this->service->calculateItemAmount('per_piece', 0.50, 49000, 500, 'KG');
        $this->assertEquals(24500.00, $perPieceAmount);

        // Per KG: 500 KG @ ₹10 = ₹5,000
        $perKgAmount = $this->service->calculateItemAmount('per_kg', 10, 49000, 500, 'KG');
        $this->assertEquals(5000.00, $perKgAmount);

        // Fixed: ₹3,500
        $fixedAmount = $this->service->calculateItemAmount('fixed', 3500, 49000, 500, 'KG');
        $this->assertEquals(3500.00, $fixedAmount);
    }

    /**
     * Test 6: Order Totals with multi-product items and financial adjustments
     */
    public function test_order_totals_calculation(): void
    {
        $itemA = $this->service->calculateItem([
            'received_weight'      => 500,
            'received_weight_unit' => 'KG',
            'product_weight'       => 10,
            'product_weight_unit'  => 'Gram',
            'wastage_type'         => 'percentage',
            'wastage_percentage'   => 2,
            'rate_type'            => 'per_piece',
            'rate'                 => 0.50,
        ], 'floor');

        $itemB = $this->service->calculateItem([
            'received_weight'      => 200,
            'received_weight_unit' => 'KG',
            'product_weight'       => 20,
            'product_weight_unit'  => 'Gram',
            'wastage_type'         => 'percentage',
            'wastage_percentage'   => 1,
            'rate_type'            => 'per_piece',
            'rate'                 => 0.75,
        ], 'floor');

        $totals = $this->service->calculateOrderTotals(
            [$itemA, $itemB],
            additionalCharges: 500,
            discount: 200,
            tax: 180,
            paidAmount: 10000
        );

        $this->assertEquals(700.00, $totals['total_received_weight_kg']);
        $this->assertEquals(60000.00, $totals['total_gross_pieces']); // 50,000 + 10,000
        $this->assertEquals(1100.00, $totals['total_wastage_pieces']); // 1,000 + 100
        $this->assertEquals(58900.00, $totals['total_net_pieces']); // 49,000 + 9,900
        $this->assertEquals(31925.00, $totals['subtotal']); // 24,500 + 7,425
        $this->assertEquals(32405.00, $totals['grand_total']); // 31,925 + 500 - 200 + 180
        $this->assertEquals(22405.00, $totals['balance_amount']); // 32,405 - 10,000
    }
}
