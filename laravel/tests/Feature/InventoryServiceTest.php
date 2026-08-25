<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockAdjustment;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\InventoryValuationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $inventoryService;
    protected InventoryValuationService $valuationService;
    protected Warehouse $warehouse;
    protected Unit $unitPcs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = app(InventoryService::class);
        $this->valuationService = app(InventoryValuationService::class);

        $this->warehouse = Warehouse::create([
            'name'       => 'Test Main Plant',
            'code'       => 'WH-TEST-1',
            'location'   => 'Ahmedabad',
            'is_primary' => true,
            'status'     => 'active',
        ]);

        $this->unitPcs = Unit::create([
            'name'      => 'Piece',
            'code'      => 'PCS',
            'symbol'    => 'pcs',
            'is_active' => true,
        ]);
    }

    public function test_opening_stock_creates_ledger_entry_and_sets_balance()
    {
        $product = Product::create([
            'name'           => '1000ml HDPE Jar',
            'sku'            => 'SGP-JAR-1000',
            'unit'           => 'PCS',
            'price'          => 25.00,
            'purchase_rate'  => 18.00,
            'opening_stock'  => 500.00,
            'stock_quantity' => 0.00,
            'reorder_level'  => 100.00,
            'gst_rate'       => 18.00,
            'warehouse_id'   => $this->warehouse->id,
        ]);

        $ledger = $this->inventoryService->recordOpeningStock($product, 500.00, 18.00);

        $product->refresh();
        $this->assertEquals(500.00, (float) $product->stock_quantity);
        $this->assertEquals(18.00, (float) $product->average_cost);
        $this->assertEquals('Opening Stock', $ledger->transaction_type);
        $this->assertEquals(500.00, (float) $ledger->quantity_in);
        $this->assertEquals(500.00, (float) $ledger->new_stock);
    }

    public function test_purchase_updates_stock_and_calculates_weighted_average_cost()
    {
        // 1. Initial 100 units @ 10.00 each (Total = 1,000)
        $product = Product::create([
            'name'           => '500ml Spray Bottle',
            'sku'            => 'SGP-BTL-500',
            'unit'           => 'PCS',
            'price'          => 20.00,
            'purchase_rate'  => 10.00,
            'average_cost'   => 10.00,
            'stock_quantity' => 100.00,
            'reorder_level'  => 50.00,
            'gst_rate'       => 18.00,
            'warehouse_id'   => $this->warehouse->id,
        ]);

        // 2. Inward Purchase: 100 units @ 20.00 each (Total = 2,000)
        // Weighted Average Cost = (100 * 10 + 100 * 20) / 200 = 3,000 / 200 = 15.00
        $this->inventoryService->recordPurchase(
            $product,
            100.00,
            20.00,
            'PO-2026-001',
            1
        );

        $product->refresh();
        $this->assertEquals(200.00, (float) $product->stock_quantity);
        $this->assertEquals(15.00, (float) $product->average_cost);
    }

    public function test_sale_decrements_stock_and_records_outward_ledger()
    {
        $product = Product::create([
            'name'           => '200ml Flip Cap Bottle',
            'sku'            => 'SGP-BTL-200',
            'unit'           => 'PCS',
            'price'          => 12.00,
            'purchase_rate'  => 8.00,
            'average_cost'   => 8.00,
            'stock_quantity' => 50.00,
            'reorder_level'  => 10.00,
            'gst_rate'       => 18.00,
            'warehouse_id'   => $this->warehouse->id,
        ]);

        $ledger = $this->inventoryService->recordSale(
            $product,
            20.00,
            12.00,
            'INV-2026-001',
            1
        );

        $product->refresh();
        $this->assertEquals(30.00, (float) $product->stock_quantity);
        $this->assertEquals('Sales', $ledger->transaction_type);
        $this->assertEquals(20.00, (float) $ledger->quantity_out);
        $this->assertEquals(30.00, (float) $ledger->new_stock);
    }

    public function test_sale_fails_on_insufficient_stock_when_negative_disallowed()
    {
        $product = Product::create([
            'name'           => 'Limited Cap',
            'sku'            => 'SGP-CAP-01',
            'unit'           => 'PCS',
            'price'          => 5.00,
            'stock_quantity' => 10.00,
            'gst_rate'       => 18.00,
            'warehouse_id'   => $this->warehouse->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->inventoryService->recordSale(
            $product,
            25.00,
            5.00,
            'INV-FAIL',
            99
        );
    }

    public function test_stock_adjustment_reconciles_physical_and_system_counts()
    {
        $product = Product::create([
            'name'           => 'Container Lid 80mm',
            'sku'            => 'SGP-LID-80',
            'unit'           => 'PCS',
            'price'          => 4.00,
            'stock_quantity' => 100.00,
            'gst_rate'       => 18.00,
            'warehouse_id'   => $this->warehouse->id,
        ]);

        // Physical count reveals 95 pcs (-5 discrepancy)
        $ledger = $this->inventoryService->recordAdjustment(
            $product,
            100.00,
            95.00,
            'Physical Count Discrepancy',
            'ADJ-001',
            1
        );

        $product->refresh();
        $this->assertEquals(95.00, (float) $product->stock_quantity);
        $this->assertEquals(5.00, (float) $ledger->quantity_out);
        $this->assertEquals('Stock Adjustment', $ledger->transaction_type);
    }

    public function test_inter_warehouse_transfer_preserves_overall_stock()
    {
        $warehouse2 = Warehouse::create([
            'name'     => 'Secondary Plant Warehouse',
            'code'     => 'WH-TEST-2',
            'status'   => 'active',
        ]);

        $product = Product::create([
            'name'           => '5 Liter Jerry Can',
            'sku'            => 'SGP-CAN-5L',
            'unit'           => 'PCS',
            'price'          => 60.00,
            'stock_quantity' => 200.00,
            'gst_rate'       => 18.00,
            'warehouse_id'   => $this->warehouse->id,
        ]);

        $result = $this->inventoryService->recordTransfer(
            $product,
            $this->warehouse->id,
            $warehouse2->id,
            50.00,
            'TRF-001',
            1
        );

        $this->assertEquals('Stock Transfer Out', $result['out']->transaction_type);
        $this->assertEquals('Stock Transfer In', $result['in']->transaction_type);
        $this->assertEquals(50.00, (float) $result['out']->quantity_out);
        $this->assertEquals(50.00, (float) $result['in']->quantity_in);
    }

    public function test_low_stock_and_reorder_level_alert()
    {
        Product::create([
            'name'           => 'Low Stock Item',
            'sku'            => 'SGP-LOW-1',
            'unit'           => 'PCS',
            'price'          => 10.00,
            'purchase_rate'  => 7.00,
            'stock_quantity' => 15.00,
            'reorder_level'  => 50.00,
            'minimum_stock'  => 20.00,
            'maximum_stock'  => 100.00,
            'gst_rate'       => 18.00,
            'is_active'      => true,
            'warehouse_id'   => $this->warehouse->id,
        ]);

        $alerts = $this->valuationService->getLowStockAlerts();
        $this->assertCount(1, $alerts);
        $this->assertEquals(15.00, $alerts->first()['current_stock']);
        $this->assertEquals(85.00, $alerts->first()['suggested_qty']); // 100 max - 15 current
    }
}
