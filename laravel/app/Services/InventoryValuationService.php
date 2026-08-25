<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLedger;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InventoryValuationService
{
    /**
     * Get Complete Inventory Valuation Dataset.
     */
    public function getValuationReport(): array
    {
        $products = Product::with('category', 'warehouse')
            ->orderBy('name')
            ->get();

        $totalStockQty = 0;
        $totalValuation = 0;
        $totalRetailValue = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        $items = $products->map(function (Product $p) use (&$totalStockQty, &$totalValuation, &$totalRetailValue, &$lowStockCount, &$outOfStockCount) {
            $stock = (float) $p->stock_quantity;
            $avgCost = (float) $p->average_cost > 0
                ? (float) $p->average_cost
                : ((float) $p->purchase_rate > 0 ? (float) $p->purchase_rate : (float) $p->price);

            $salesRate = (float) $p->sales_rate > 0 ? (float) $p->sales_rate : (float) $p->price;
            $stockValue = round($stock * $avgCost, 2);
            $retailValue = round($stock * $salesRate, 2);

            $totalStockQty += $stock;
            $totalValuation += $stockValue;
            $totalRetailValue += $retailValue;

            if ($stock <= 0) {
                $outOfStockCount++;
            } elseif ($p->reorder_level > 0 && $stock <= (float) $p->reorder_level) {
                $lowStockCount++;
            }

            return [
                'id'             => $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku,
                'category'       => $p->category->name ?? 'Uncategorized',
                'unit'           => $p->unit ?: 'PCS',
                'current_stock'  => $stock,
                'average_cost'   => $avgCost,
                'purchase_rate'  => (float) $p->purchase_rate,
                'sales_rate'     => $salesRate,
                'stock_value'    => $stockValue,
                'retail_value'   => $retailValue,
                'reorder_level'  => (float) $p->reorder_level,
                'status'         => $p->stock_status,
            ];
        });

        return [
            'items'              => $items,
            'total_products'     => $products->count(),
            'total_stock_qty'    => round($totalStockQty, 2),
            'total_valuation'    => round($totalValuation, 2),
            'total_retail_value' => round($totalRetailValue, 2),
            'low_stock_count'    => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];
    }

    /**
     * Identify Dead Stock (Products with 0 transactions in $days days).
     */
    public function getDeadStockReport(int $days = 60): Collection
    {
        $cutoffDate = Carbon::now()->subDays($days)->toDateString();

        $activeProductIds = StockLedger::whereDate('transaction_date', '>=', $cutoffDate)
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        return Product::whereNotIn('id', $activeProductIds)
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->get()
            ->map(function ($p) use ($cutoffDate) {
                $lastTx = StockLedger::where('product_id', $p->id)->latest('transaction_date')->first();
                $avgCost = (float) $p->average_cost > 0 ? (float) $p->average_cost : (float) $p->purchase_rate;
                return [
                    'product'            => $p,
                    'current_stock'      => (float) $p->stock_quantity,
                    'stock_value'        => round((float) $p->stock_quantity * $avgCost, 2),
                    'last_movement_date' => $lastTx ? $lastTx->transaction_date->format('Y-m-d') : 'Never',
                ];
            });
    }

    /**
     * Get Low Stock & Reorder Alert List.
     */
    public function getLowStockAlerts(): Collection
    {
        return Product::where('is_active', true)
            ->where(function ($q) {
                $q->whereColumn('stock_quantity', '<=', 'reorder_level')
                  ->orWhereColumn('stock_quantity', '<=', 'minimum_stock')
                  ->orWhere('stock_quantity', '<=', 0);
            })
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->get()
            ->map(function (Product $p) {
                $stock = (float) $p->stock_quantity;
                $reorder = (float) $p->reorder_level;
                $min = (float) $p->minimum_stock;
                $max = (float) $p->maximum_stock;

                // Suggested purchase quantity = (Max Stock or Reorder * 2) - Current Stock
                $target = $max > 0 ? $max : ($reorder > 0 ? $reorder * 2 : 100);
                $suggestedQty = max(0, $target - $stock);

                return [
                    'product'          => $p,
                    'current_stock'    => $stock,
                    'reorder_level'    => $reorder,
                    'minimum_stock'    => $min,
                    'maximum_stock'    => $max,
                    'suggested_qty'    => $suggestedQty,
                    'status'           => $p->stock_status,
                    'purchase_rate'    => (float) $p->purchase_rate,
                    'estimated_cost'   => round($suggestedQty * (float) $p->purchase_rate, 2),
                ];
            });
    }
}
