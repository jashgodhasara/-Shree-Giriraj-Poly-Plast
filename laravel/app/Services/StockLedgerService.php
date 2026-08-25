<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLedger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class StockLedgerService
{
    /**
     * Build filtered query for Stock Ledger entries.
     */
    public function getFilteredQuery(array $filters): Builder
    {
        $query = StockLedger::with(['product', 'warehouse', 'user'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        if (!empty($filters['reference_number'])) {
            $query->where('reference_number', 'LIKE', '%' . $filters['reference_number'] . '%');
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', Carbon::parse($filters['date_from'])->toDateString());
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', Carbon::parse($filters['date_to'])->toDateString());
        }

        return $query;
    }

    /**
     * Get Paginated Ledger entries.
     */
    public function getPaginated(array $filters, int $perPage = 50): LengthAwarePaginator
    {
        return $this->getFilteredQuery($filters)->paginate($perPage);
    }

    /**
     * Generate Stock Ledger Report summary for a specific product.
     */
    public function getProductLedgerSummary(Product $product): array
    {
        $ledgers = StockLedger::where('product_id', $product->id)->get();

        $totalIn = $ledgers->sum('quantity_in');
        $totalOut = $ledgers->sum('quantity_out');
        $currentStock = (float) $product->stock_quantity;
        $avgCost = (float) $product->average_cost > 0 ? (float) $product->average_cost : (float) $product->purchase_rate;
        $valuation = round($currentStock * $avgCost, 2);

        return [
            'product'       => $product,
            'total_in'      => $totalIn,
            'total_out'     => $totalOut,
            'current_stock' => $currentStock,
            'average_cost'  => $avgCost,
            'total_value'   => $valuation,
            'total_entries' => $ledgers->count(),
        ];
    }
}
