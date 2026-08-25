<?php

namespace App\Services;

use App\Models\InventoryAuditLog;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryService
{
    /**
     * Centralized Atomic Method for recording every stock movement in the ERP.
     *
     * @param array $params [
     *   'product_id'          => int (required),
     *   'warehouse_id'        => int|null,
     *   'transaction_type'    => string (Opening Stock, Purchase, Purchase Return, Sales, Sales Return, Stock Adjustment, Stock Transfer In, Stock Transfer Out, Job Work Issue, Job Work Receive, Production, Consumption, Damage, Wastage),
     *   'quantity'            => float (positive number),
     *   'unit'                => string|null,
     *   'direction'           => 'IN'|'OUT' (determines stock increment or decrement),
     *   'rate'                => float|null,
     *   'amount'              => float|null,
     *   'reference_module'    => string|null,
     *   'reference_id'        => int|null,
     *   'reference_number'    => string|null,
     *   'transaction_date'    => string|null (Y-m-d),
     *   'remarks'             => string|null,
     *   'user_id'             => int|null,
     *   'allow_negative'      => bool (default false),
     * ]
     * @return StockLedger
     */
    public function recordTransaction(array $params): StockLedger
    {
        $productId = $params['product_id'] ?? null;
        if (!$productId) {
            throw new InvalidArgumentException('Product ID is required for inventory transaction.');
        }

        $qty = abs((float) ($params['quantity'] ?? 0));
        if ($qty <= 0) {
            throw new InvalidArgumentException('Transaction quantity must be greater than zero.');
        }

        $direction = strtoupper($params['direction'] ?? 'IN');
        if (!in_array($direction, ['IN', 'OUT'])) {
            throw new InvalidArgumentException('Transaction direction must be IN or OUT.');
        }

        $transactionType = $params['transaction_type'] ?? 'Stock Adjustment';
        $transactionDate = $params['transaction_date'] ?: now()->toDateString();
        $rate            = isset($params['rate']) ? (float) $params['rate'] : 0.0;
        $amount          = isset($params['amount']) ? (float) $params['amount'] : round($qty * $rate, 2);
        $userId          = $params['user_id'] ?? Auth::id();
        $allowNegative   = $params['allow_negative'] ?? false;
        $warehouseId     = $params['warehouse_id'] ?? null;
        $unit            = $params['unit'] ?? 'PCS';

        return DB::transaction(function () use (
            $productId, $warehouseId, $qty, $direction, $transactionType,
            $transactionDate, $rate, $amount, $userId, $allowNegative,
            $unit, $params
        ) {
            // Pessimistic Row Lock on Product
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $previousStock = (float) $product->stock_quantity;
            $previousAvgCost = (float) $product->average_cost > 0
                ? (float) $product->average_cost
                : ((float) $product->purchase_rate > 0 ? (float) $product->purchase_rate : (float) $product->price);

            if ($direction === 'OUT') {
                if (!$allowNegative && ($previousStock - $qty) < -0.0001) {
                    throw new RuntimeException(
                        "Insufficient stock for product '{$product->name}' (SKU: {$product->sku}). Available: {$previousStock}, Requested: {$qty}"
                    );
                }
                $quantityIn  = 0.0;
                $quantityOut = $qty;
                $stockChange = -$qty;
                $newStock    = round($previousStock - $qty, 4);
                $newAvgCost  = $previousAvgCost; // Outward movements don't change unit cost
            } else {
                // IN
                $quantityIn  = $qty;
                $quantityOut = 0.0;
                $stockChange = $qty;
                $newStock    = round($previousStock + $qty, 4);

                // Weighted Average Cost recalculation on inward purchases/production
                if ($rate > 0 && in_array($transactionType, ['Purchase', 'Opening Stock', 'Production', 'Stock Adjustment'])) {
                    $totalExistingValue = max(0, $previousStock) * $previousAvgCost;
                    $totalInwardValue   = $qty * $rate;
                    $totalCombinedQty   = max(0, $previousStock) + $qty;

                    $newAvgCost = $totalCombinedQty > 0
                        ? round(($totalExistingValue + $totalInwardValue) / $totalCombinedQty, 4)
                        : $rate;
                } else {
                    $newAvgCost = $previousAvgCost;
                }
            }

            // Create Immutable Stock Ledger Record
            $ledger = StockLedger::create([
                'product_id'         => $product->id,
                'warehouse_id'       => $warehouseId ?: $product->warehouse_id,
                'transaction_date'   => $transactionDate,
                'transaction_type'   => $transactionType,
                'reference_module'   => $params['reference_module'] ?? null,
                'reference_id'       => $params['reference_id'] ?? null,
                'reference_number'   => $params['reference_number'] ?? null,
                'quantity_in'        => $quantityIn,
                'quantity_out'       => $quantityOut,
                'unit'               => $unit,
                'converted_quantity' => $qty,
                'rate'               => $rate,
                'amount'             => $amount,
                'previous_stock'     => $previousStock,
                'stock_change'       => $stockChange,
                'new_stock'          => $newStock,
                'average_cost_after' => $newAvgCost,
                'user_id'            => $userId,
                'remarks'            => $params['remarks'] ?? null,
            ]);

            // Update Product Master stock & average cost
            $product->stock_quantity = $newStock;
            if ($newAvgCost > 0) {
                $product->average_cost = $newAvgCost;
            }
            if ($rate > 0 && in_array($transactionType, ['Purchase', 'Opening Stock'])) {
                $product->purchase_rate = $rate;
            }
            $product->save();

            // Audit Trail
            InventoryAuditLog::create([
                'user_id'          => $userId,
                'action'           => "STOCK_{$direction}_{$transactionType}",
                'entity_type'      => Product::class,
                'entity_id'        => $product->id,
                'reference_number' => $params['reference_number'] ?? $ledger->id,
                'old_values'       => ['stock_quantity' => $previousStock, 'average_cost' => $previousAvgCost],
                'new_values'       => ['stock_quantity' => $newStock, 'average_cost' => $newAvgCost, 'ledger_id' => $ledger->id],
                'ip_address'       => request()->ip() ?? '127.0.0.1',
            ]);

            return $ledger;
        });
    }

    /**
     * Record Opening Stock for a Product.
     */
    public function recordOpeningStock(Product $product, float $quantity, float $rate, ?string $date = null, ?string $remarks = null): StockLedger
    {
        return $this->recordTransaction([
            'product_id'       => $product->id,
            'warehouse_id'     => $product->warehouse_id,
            'transaction_type' => 'Opening Stock',
            'quantity'         => $quantity,
            'unit'             => $product->unit ?: 'PCS',
            'direction'        => 'IN',
            'rate'             => $rate,
            'amount'           => round($quantity * $rate, 2),
            'reference_module' => 'Products',
            'reference_id'     => $product->id,
            'reference_number' => 'OPENING-' . $product->id,
            'transaction_date' => $date ?: now()->toDateString(),
            'remarks'          => $remarks ?: 'Initial opening balance',
        ]);
    }

    /**
     * Record Inward Purchase.
     */
    public function recordPurchase(Product $product, float $quantity, float $rate, string $poNumber, int $poId, ?string $date = null, ?string $remarks = null): StockLedger
    {
        return $this->recordTransaction([
            'product_id'       => $product->id,
            'warehouse_id'     => $product->warehouse_id,
            'transaction_type' => 'Purchase',
            'quantity'         => $quantity,
            'unit'             => $product->unit ?: 'PCS',
            'direction'        => 'IN',
            'rate'             => $rate,
            'amount'           => round($quantity * $rate, 2),
            'reference_module' => 'PurchaseOrders',
            'reference_id'     => $poId,
            'reference_number' => $poNumber,
            'transaction_date' => $date ?: now()->toDateString(),
            'remarks'          => $remarks ?: "Purchase receipt for PO #{$poNumber}",
        ]);
    }

    /**
     * Record Outward Sale.
     */
    public function recordSale(Product $product, float $quantity, float $unitPrice, string $invoiceNumber, int $invoiceId, ?string $date = null, ?string $remarks = null, bool $allowNegative = false): StockLedger
    {
        return $this->recordTransaction([
            'product_id'       => $product->id,
            'warehouse_id'     => $product->warehouse_id,
            'transaction_type' => 'Sales',
            'quantity'         => $quantity,
            'unit'             => $product->unit ?: 'PCS',
            'direction'        => 'OUT',
            'rate'             => $unitPrice,
            'amount'           => round($quantity * $unitPrice, 2),
            'reference_module' => 'Invoices',
            'reference_id'     => $invoiceId,
            'reference_number' => $invoiceNumber,
            'transaction_date' => $date ?: now()->toDateString(),
            'remarks'          => $remarks ?: "Sales invoice #{$invoiceNumber}",
            'allow_negative'   => $allowNegative,
        ]);
    }

    /**
     * Record Sales Return / Credit Note.
     */
    public function recordSaleReturn(Product $product, float $quantity, float $unitPrice, string $referenceNumber, ?int $referenceId = null, ?string $date = null): StockLedger
    {
        return $this->recordTransaction([
            'product_id'       => $product->id,
            'warehouse_id'     => $product->warehouse_id,
            'transaction_type' => 'Sales Return',
            'quantity'         => $quantity,
            'unit'             => $product->unit ?: 'PCS',
            'direction'        => 'IN',
            'rate'             => $unitPrice,
            'amount'           => round($quantity * $unitPrice, 2),
            'reference_module' => 'Invoices',
            'reference_id'     => $referenceId,
            'reference_number' => $referenceNumber,
            'transaction_date' => $date ?: now()->toDateString(),
            'remarks'          => "Sales return for #{$referenceNumber}",
        ]);
    }

    /**
     * Record Stock Adjustment.
     */
    public function recordAdjustment(Product $product, float $systemStock, float $physicalStock, string $reason, string $adjNumber, int $adjId, ?string $remarks = null): StockLedger
    {
        $diff = round($physicalStock - $systemStock, 4);
        $direction = $diff >= 0 ? 'IN' : 'OUT';
        $qty = abs($diff);
        $rate = (float) $product->average_cost > 0 ? (float) $product->average_cost : (float) $product->price;

        return $this->recordTransaction([
            'product_id'       => $product->id,
            'warehouse_id'     => $product->warehouse_id,
            'transaction_type' => 'Stock Adjustment',
            'quantity'         => $qty,
            'unit'             => $product->unit ?: 'PCS',
            'direction'        => $direction,
            'rate'             => $rate,
            'amount'           => round($qty * $rate, 2),
            'reference_module' => 'StockAdjustments',
            'reference_id'     => $adjId,
            'reference_number' => $adjNumber,
            'transaction_date' => now()->toDateString(),
            'remarks'          => "Adjustment ({$reason}): System {$systemStock} -> Physical {$physicalStock}. " . ($remarks ?? ''),
        ]);
    }

    /**
     * Record Warehouse Transfer Out & In.
     */
    public function recordTransfer(Product $product, int $fromWarehouseId, int $toWarehouseId, float $quantity, string $transferNumber, int $transferId): array
    {
        return DB::transaction(function () use ($product, $fromWarehouseId, $toWarehouseId, $quantity, $transferNumber, $transferId) {
            $rate = (float) $product->average_cost > 0 ? (float) $product->average_cost : (float) $product->price;

            // 1. Transfer Out from source warehouse
            $outLedger = $this->recordTransaction([
                'product_id'       => $product->id,
                'warehouse_id'     => $fromWarehouseId,
                'transaction_type' => 'Stock Transfer Out',
                'quantity'         => $quantity,
                'unit'             => $product->unit ?: 'PCS',
                'direction'        => 'OUT',
                'rate'             => $rate,
                'amount'           => round($quantity * $rate, 2),
                'reference_module' => 'StockTransfers',
                'reference_id'     => $transferId,
                'reference_number' => $transferNumber,
                'transaction_date' => now()->toDateString(),
                'remarks'          => "Transfer to Warehouse #{$toWarehouseId}",
            ]);

            // 2. Transfer In to destination warehouse
            $inLedger = $this->recordTransaction([
                'product_id'       => $product->id,
                'warehouse_id'     => $toWarehouseId,
                'transaction_type' => 'Stock Transfer In',
                'quantity'         => $quantity,
                'unit'             => $product->unit ?: 'PCS',
                'direction'        => 'IN',
                'rate'             => $rate,
                'amount'           => round($quantity * $rate, 2),
                'reference_module' => 'StockTransfers',
                'reference_id'     => $transferId,
                'reference_number' => $transferNumber,
                'transaction_date' => now()->toDateString(),
                'remarks'          => "Transfer from Warehouse #{$fromWarehouseId}",
            ]);

            return ['out' => $outLedger, 'in' => $inLedger];
        });
    }

    /**
     * Safe Reversal of an existing transaction by Reference (e.g. invoice or PO deletion).
     */
    public function reverseByReference(string $referenceModule, int $referenceId, string $reason = 'Reversal'): int
    {
        $ledgers = StockLedger::where('reference_module', $referenceModule)
            ->where('reference_id', $referenceId)
            ->get();

        $count = 0;
        foreach ($ledgers as $ledger) {
            $oppositeDirection = $ledger->quantity_in > 0 ? 'OUT' : 'IN';
            $qty = $ledger->quantity_in > 0 ? (float) $ledger->quantity_in : (float) $ledger->quantity_out;

            $this->recordTransaction([
                'product_id'       => $ledger->product_id,
                'warehouse_id'     => $ledger->warehouse_id,
                'transaction_type' => 'Stock Adjustment',
                'quantity'         => $qty,
                'unit'             => $ledger->unit,
                'direction'        => $oppositeDirection,
                'rate'             => (float) $ledger->rate,
                'amount'           => (float) $ledger->amount,
                'reference_module' => $referenceModule,
                'reference_id'     => $referenceId,
                'reference_number' => 'REV-' . $ledger->reference_number,
                'transaction_date' => now()->toDateString(),
                'remarks'          => "Automated {$reason} for {$ledger->transaction_type} ({$ledger->reference_number})",
            ]);
            $count++;
        }

        return $count;
    }
}
