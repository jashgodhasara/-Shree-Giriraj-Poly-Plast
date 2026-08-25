<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Check if a product with matching SKU, code, or barcode already exists.
     */
    public function checkDuplicate(?string $sku, ?string $barcode = null, ?string $name = null, ?int $excludeId = null): ?Product
    {
        $query = Product::query();

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->where(function ($q) use ($sku, $barcode, $name) {
            if ($sku) {
                $q->orWhere('sku', trim($sku));
            }
            if ($barcode) {
                $q->orWhere('barcode', trim($barcode));
            }
            if ($name) {
                $q->orWhere('name', trim($name));
            }
        })->first();
    }

    /**
     * Bulk Import Products from CSV/Array.
     */
    public function bulkImport(array $rows, ?int $userId = null): array
    {
        $successCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // considering 1-based index with header
            $name = trim($row['name'] ?? ($row['Product Name'] ?? ''));

            if (empty($name)) {
                $errors[] = "Row #{$line}: Product Name is required.";
                continue;
            }

            $sku = trim($row['sku'] ?? ($row['SKU'] ?? ''));
            if (empty($sku)) {
                $sku = Product::generateSku();
            }

            $existing = Product::where('sku', $sku)->first();
            if ($existing) {
                $errors[] = "Row #{$line}: SKU '{$sku}' already exists.";
                continue;
            }

            // Category resolution
            $categoryName = trim($row['category'] ?? ($row['Category'] ?? ''));
            $categoryId = null;
            if ($categoryName) {
                $category = ProductCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['code' => strtoupper(Str::slug($categoryName))]
                );
                $categoryId = $category->id;
            }

            $price = (float) ($row['price'] ?? ($row['Sales Rate'] ?? ($row['Price'] ?? 0)));
            $purchaseRate = (float) ($row['purchase_rate'] ?? ($row['Purchase Rate'] ?? 0));
            $openingStock = (float) ($row['opening_stock'] ?? ($row['Opening Stock'] ?? 0));
            $gstRate = (float) ($row['gst_rate'] ?? ($row['GST %'] ?? 18.00));
            $hsnCode = trim($row['hsn_code'] ?? ($row['HSN Code'] ?? ''));
            $unit = trim($row['unit'] ?? ($row['Unit'] ?? 'PCS')) ?: 'PCS';
            $reorderLevel = (float) ($row['reorder_level'] ?? ($row['Reorder Level'] ?? 0));
            $minStock = (float) ($row['minimum_stock'] ?? ($row['Minimum Stock'] ?? 0));
            $maxStock = (float) ($row['maximum_stock'] ?? ($row['Maximum Stock'] ?? 0));

            $product = Product::create([
                'name'            => $name,
                'sku'             => $sku,
                'category_id'     => $categoryId,
                'unit'            => $unit,
                'unit_type'       => $unit,
                'price'           => $price,
                'sales_rate'      => $price,
                'purchase_rate'   => $purchaseRate,
                'average_cost'    => $purchaseRate > 0 ? $purchaseRate : $price,
                'gst_rate'        => $gstRate,
                'hsn_code'        => $hsnCode,
                'opening_stock'   => $openingStock,
                'stock_quantity'  => $openingStock,
                'minimum_stock'   => $minStock,
                'maximum_stock'   => $maxStock,
                'reorder_level'   => $reorderLevel,
                'is_active'       => true,
            ]);

            // Post Opening Stock Ledger if stock > 0
            if ($openingStock > 0) {
                $this->inventoryService->recordOpeningStock(
                    $product,
                    $openingStock,
                    $purchaseRate > 0 ? $purchaseRate : $price,
                    now()->toDateString(),
                    'Bulk CSV Imported Opening Stock'
                );
            }

            $successCount++;
        }

        return [
            'success_count' => $successCount,
            'errors'        => $errors,
        ];
    }
}
