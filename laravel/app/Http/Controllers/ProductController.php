<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItem;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrderItem;
use App\Models\StockLedger;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\JobWorkCalculationService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    protected InventoryService $inventoryService;
    protected ProductService $productService;

    public function __construct(InventoryService $inventoryService, ProductService $productService)
    {
        $this->inventoryService = $inventoryService;
        $this->productService   = $productService;
    }

    public function index(Request $request)
    {
        $search       = $request->get('search');
        $categoryId   = $request->get('category_id');
        $stockStatus  = $request->get('stock_status');
        $productType  = $request->get('product_type');
        $sortBy       = $request->get('sort_by', 'created_at');
        $sortOrder    = $request->get('sort_order', 'desc');

        $query = Product::with(['category', 'warehouse'])->latest();

        if ($search) {
            $query->search($search);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($productType) {
            $query->where('product_type', $productType);
        }

        if ($stockStatus) {
            if ($stockStatus === 'in_stock') {
                $query->where('stock_quantity', '>', 0)
                      ->where(function($q) {
                          $q->whereNull('reorder_level')
                            ->orWhereColumn('stock_quantity', '>', 'reorder_level');
                      });
            } elseif ($stockStatus === 'low_stock') {
                $query->where('stock_quantity', '>', 0)
                      ->whereColumn('stock_quantity', '<=', 'reorder_level');
            } elseif ($stockStatus === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        // Sorting
        $allowedSorts = ['name', 'sku', 'stock_quantity', 'price', 'purchase_rate', 'average_cost', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $products = $query->paginate(25)->appends($request->query());

        if ($request->wantsJson()) {
            return response()->json($products);
        }

        $categories = ProductCategory::active()->orderBy('name')->get();
        $units      = Unit::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $materials  = Material::orderBy('name')->get();

        // Summary counts
        $totalProductsCount = Product::count();
        $lowStockCount      = Product::lowStock()->count();
        $outOfStockCount    = Product::outOfStock()->count();
        $totalStockValue    = (float) Product::select(DB::raw('SUM(stock_quantity * CASE WHEN average_cost > 0 THEN average_cost WHEN purchase_rate > 0 THEN purchase_rate ELSE price END) as val'))->value('val');

        return view('products.index', compact(
            'products', 'categories', 'units', 'warehouses', 'materials',
            'search', 'categoryId', 'stockStatus', 'productType', 'sortBy', 'sortOrder',
            'totalProductsCount', 'lowStockCount', 'outOfStockCount', 'totalStockValue'
        ));
    }

    public function show(Request $request, Product $product)
    {
        $product->load(['category', 'material', 'warehouse']);

        if ($request->wantsJson()) {
            return response()->json($product);
        }

        // Stock ledger history
        $stockLedgers = StockLedger::where('product_id', $product->id)
            ->with(['warehouse', 'user'])
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(30, ['*'], 'ledger_page');

        // Recent sales history
        $salesHistory = InvoiceItem::where('product_id', $product->id)
            ->with(['invoice.customer'])
            ->latest('id')
            ->take(15)
            ->get();

        // Summary calculations
        $totalIn = (float) StockLedger::where('product_id', $product->id)->sum('quantity_in');
        $totalOut = (float) StockLedger::where('product_id', $product->id)->sum('quantity_out');
        $totalSalesQty = (float) InvoiceItem::where('product_id', $product->id)->sum('quantity');

        return view('products.show', compact('product', 'stockLedgers', 'salesHistory', 'totalIn', 'totalOut', 'totalSalesQty'));
    }

    public function store(Request $request, JobWorkCalculationService $calcService)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'category_id'         => 'nullable|exists:product_categories,id',
            'subcategory'         => 'nullable|string|max:150',
            'sku'                 => 'nullable|string|max:100|unique:products,sku',
            'product_code'        => 'nullable|string|max:100',
            'product_type'        => 'nullable|string|max:100',
            'material_id'         => 'nullable|exists:materials,id',
            'brand'               => 'nullable|string|max:150',
            'unit'                => 'nullable|string|max:50',
            'purchase_unit'       => 'nullable|string|max:50',
            'sales_unit'          => 'nullable|string|max:50',
            'conversion_factor'   => 'nullable|numeric|min:0.0001',
            'weight_per_piece'    => 'nullable|numeric|min:0',
            'weight_unit'         => 'nullable|in:Gram,KG,Milligram,Ton',
            'wastage_percentage'  => 'nullable|numeric|min:0|max:100',
            'fixed_wastage'       => 'nullable|numeric|min:0',
            'job_work_applicable' => 'nullable|boolean',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric|min:0',
            'purchase_rate'       => 'nullable|numeric|min:0',
            'sales_rate'          => 'nullable|numeric|min:0',
            'wholesale_rate'      => 'nullable|numeric|min:0',
            'mrp'                 => 'nullable|numeric|min:0',
            'hsn_code'            => 'nullable|string|max:20',
            'gst_rate'            => 'required|numeric|min:0|max:100',
            'barcode'             => 'nullable|string|max:100',
            'opening_stock'       => 'nullable|numeric|min:0',
            'minimum_stock'       => 'nullable|numeric|min:0',
            'maximum_stock'       => 'nullable|numeric|min:0',
            'reorder_level'       => 'nullable|numeric|min:0',
            'warehouse_id'        => 'nullable|exists:warehouses,id',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = Product::generateSku();
        }

        $validated['job_work_applicable'] = $request->boolean('job_work_applicable', true);
        $validated['is_active']           = $request->boolean('is_active', true);
        $validated['unit']                = $validated['unit'] ?: 'PCS';
        $validated['unit_type']           = $validated['unit'];
        $validated['weight_unit']         = $validated['weight_unit'] ?: 'Gram';
        $validated['weight_per_piece']    = (float) ($validated['weight_per_piece'] ?? 0);
        $validated['weight_in_grams']     = $calcService->convertToGrams($validated['weight_per_piece'], $validated['weight_unit']);
        $validated['conversion_factor']   = (float) ($validated['conversion_factor'] ?? 1.0);
        $validated['sales_rate']          = !empty($validated['sales_rate']) ? $validated['sales_rate'] : $validated['price'];
        
        $openingStock = (float) ($validated['opening_stock'] ?? 0);
        $purchaseRate = (float) ($validated['purchase_rate'] ?? 0);
        $validated['average_cost'] = $purchaseRate > 0 ? $purchaseRate : (float) $validated['price'];
        $validated['stock_quantity'] = $openingStock;

        if ($request->hasFile('image')) {
            $dest = public_path('uploads/products');
            if (!file_exists($dest)) {
                @mkdir($dest, 0777, true);
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dest, $filename);
            $validated['image'] = 'uploads/products/' . $filename;
        }

        $product = DB::transaction(function () use ($validated, $openingStock, $purchaseRate) {
            $prod = Product::create($validated);

            // Record Opening Stock in Stock Ledger if > 0
            if ($openingStock > 0) {
                $rate = $purchaseRate > 0 ? $purchaseRate : (float) $prod->price;
                $this->inventoryService->recordOpeningStock($prod, $openingStock, $rate, now()->toDateString(), 'Initial opening stock creation');
            }

            return $prod;
        });

        return response()->json([
            'success' => true,
            'message' => "Product '{$product->name}' (SKU: {$product->sku}) created successfully.",
            'product' => $product
        ]);
    }

    public function update(Request $request, Product $product, JobWorkCalculationService $calcService)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'category_id'         => 'nullable|exists:product_categories,id',
            'subcategory'         => 'nullable|string|max:150',
            'sku'                 => 'required|string|max:100|unique:products,sku,' . $product->id,
            'product_code'        => 'nullable|string|max:100',
            'product_type'        => 'nullable|string|max:100',
            'material_id'         => 'nullable|exists:materials,id',
            'brand'               => 'nullable|string|max:150',
            'unit'                => 'nullable|string|max:50',
            'purchase_unit'       => 'nullable|string|max:50',
            'sales_unit'          => 'nullable|string|max:50',
            'conversion_factor'   => 'nullable|numeric|min:0.0001',
            'weight_per_piece'    => 'nullable|numeric|min:0',
            'weight_unit'         => 'nullable|in:Gram,KG,Milligram,Ton',
            'wastage_percentage'  => 'nullable|numeric|min:0|max:100',
            'fixed_wastage'       => 'nullable|numeric|min:0',
            'job_work_applicable' => 'nullable|boolean',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric|min:0',
            'purchase_rate'       => 'nullable|numeric|min:0',
            'sales_rate'          => 'nullable|numeric|min:0',
            'wholesale_rate'      => 'nullable|numeric|min:0',
            'mrp'                 => 'nullable|numeric|min:0',
            'hsn_code'            => 'nullable|string|max:20',
            'gst_rate'            => 'required|numeric|min:0|max:100',
            'barcode'             => 'nullable|string|max:100',
            'minimum_stock'       => 'nullable|numeric|min:0',
            'maximum_stock'       => 'nullable|numeric|min:0',
            'reorder_level'       => 'nullable|numeric|min:0',
            'warehouse_id'        => 'nullable|exists:warehouses,id',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        $validated['job_work_applicable'] = $request->boolean('job_work_applicable', true);
        $validated['is_active']           = $request->boolean('is_active', true);
        $validated['unit']                = $validated['unit'] ?: ($product->unit ?: 'PCS');
        $validated['unit_type']           = $validated['unit'];
        $validated['weight_unit']         = $validated['weight_unit'] ?: 'Gram';
        $validated['weight_per_piece']    = (float) ($validated['weight_per_piece'] ?? 0);
        $validated['weight_in_grams']     = $calcService->convertToGrams($validated['weight_per_piece'], $validated['weight_unit']);
        $validated['sales_rate']          = !empty($validated['sales_rate']) ? $validated['sales_rate'] : $validated['price'];

        if ($request->hasFile('image')) {
            $dest = public_path('uploads/products');
            if (!file_exists($dest)) {
                @mkdir($dest, 0777, true);
            }
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($dest, $filename);
            $validated['image'] = 'uploads/products/' . $filename;
        } elseif ($request->boolean('remove_image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }
            $validated['image'] = null;
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Product '{$product->name}' updated successfully.",
            'product' => $product
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->invoiceItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete product '{$product->name}' because existing Sales Invoices are linked to it.",
            ], 422);
        }

        if ($product->jobWorkOrderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete product '{$product->name}' because existing Job Work orders are linked to it.",
            ], 422);
        }

        if ($product->image && file_exists(public_path($product->image))) {
            @unlink(public_path($product->image));
        }

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
    }

    /**
     * Check duplicate product in real-time.
     */
    public function checkDuplicate(Request $request)
    {
        $dup = $this->productService->checkDuplicate(
            $request->get('sku'),
            $request->get('barcode'),
            $request->get('name'),
            $request->get('exclude_id') ? (int) $request->get('exclude_id') : null
        );

        return response()->json([
            'duplicate' => !is_null($dup),
            'product'   => $dup ? ['id' => $dup->id, 'name' => $dup->name, 'sku' => $dup->sku] : null,
        ]);
    }

    /**
     * Bulk CSV Import.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));

        if (count($csvData) < 2) {
            return response()->json(['success' => false, 'message' => 'CSV file is empty or missing data rows.'], 422);
        }

        $headers = array_map('trim', $csvData[0]);
        $rows = [];

        for ($i = 1; $i < count($csvData); $i++) {
            if (empty(array_filter($csvData[$i]))) continue;
            $row = [];
            foreach ($headers as $k => $head) {
                $row[$head] = $csvData[$i][$k] ?? '';
            }
            $rows[] = $row;
        }

        $result = $this->productService->bulkImport($rows, auth()->id());

        return response()->json([
            'success' => true,
            'message' => "Bulk import completed. {$result['success_count']} products imported successfully.",
            'errors'  => $result['errors'],
        ]);
    }

    /**
     * Bulk Export Products to CSV.
     */
    public function export(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="shreegiriraj_products_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Product ID', 'Product Name', 'SKU', 'Product Code', 'Category',
                'Product Type', 'Unit', 'Current Stock', 'Purchase Rate', 'Sales Rate (Price)',
                'Average Cost', 'Total Stock Value', 'GST Rate (%)', 'HSN Code', 'Barcode',
                'Reorder Level', 'Minimum Stock', 'Status'
            ]);

            Product::with('category')->chunk(200, function ($products) use ($handle) {
                foreach ($products as $p) {
                    $avgCost = (float) $p->average_cost > 0 ? (float) $p->average_cost : (float) $p->purchase_rate;
                    $val = round((float) $p->stock_quantity * $avgCost, 2);
                    fputcsv($handle, [
                        $p->id,
                        $p->name,
                        $p->sku,
                        $p->product_code,
                        $p->category->name ?? 'Uncategorized',
                        $p->product_type,
                        $p->unit ?: 'PCS',
                        $p->stock_quantity,
                        $p->purchase_rate,
                        $p->price,
                        $avgCost,
                        $val,
                        $p->gst_rate,
                        $p->hsn_code,
                        $p->barcode,
                        $p->reorder_level,
                        $p->minimum_stock,
                        $p->stock_status,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
