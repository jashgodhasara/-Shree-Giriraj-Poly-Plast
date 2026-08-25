<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low_stock') {
                $query->lowStock();
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->outOfStock();
            }
        }

        return ProductResource::collection($query->paginate($request->get('per_page', 50)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'nullable|exists:product_categories,id',
            'subcategory'       => 'nullable|string|max:150',
            'sku'               => 'nullable|string|max:100|unique:products,sku',
            'product_code'      => 'nullable|string|max:100',
            'product_type'      => 'nullable|string|max:100',
            'unit'              => 'nullable|string|max:50',
            'purchase_unit'     => 'nullable|string|max:50',
            'sales_unit'        => 'nullable|string|max:50',
            'conversion_factor' => 'nullable|numeric|min:0.0001',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'purchase_rate'     => 'nullable|numeric|min:0',
            'sales_rate'        => 'nullable|numeric|min:0',
            'wholesale_rate'    => 'nullable|numeric|min:0',
            'mrp'               => 'nullable|numeric|min:0',
            'hsn_code'          => 'nullable|string|max:20',
            'gst_rate'          => 'required|numeric|min:0|max:100',
            'barcode'           => 'nullable|string|max:100',
            'opening_stock'     => 'nullable|numeric|min:0',
            'minimum_stock'     => 'nullable|numeric|min:0',
            'maximum_stock'     => 'nullable|numeric|min:0',
            'reorder_level'     => 'nullable|numeric|min:0',
            'warehouse_id'      => 'nullable|exists:warehouses,id',
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = Product::generateSku();
        }

        $validated['unit']          = $validated['unit'] ?: 'PCS';
        $validated['unit_type']     = $validated['unit'];
        $validated['sales_rate']    = !empty($validated['sales_rate']) ? $validated['sales_rate'] : $validated['price'];
        $validated['average_cost']  = (float) ($validated['purchase_rate'] ?? 0) > 0 ? (float) $validated['purchase_rate'] : (float) $validated['price'];
        $openingStock               = (float) ($validated['opening_stock'] ?? 0);
        $validated['stock_quantity'] = $openingStock;
        $validated['is_active']     = true;

        $product = DB::transaction(function () use ($validated, $openingStock) {
            $prod = Product::create($validated);
            if ($openingStock > 0) {
                $rate = (float) ($prod->purchase_rate > 0 ? $prod->purchase_rate : $prod->price);
                $this->inventoryService->recordOpeningStock($prod, $openingStock, $rate, now()->toDateString(), 'API Opening Stock');
            }
            return $prod;
        });

        return new ProductResource($product->load('category'));
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load('category', 'warehouse'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'nullable|exists:product_categories,id',
            'subcategory'       => 'nullable|string|max:150',
            'sku'               => 'required|string|max:100|unique:products,sku,' . $product->id,
            'product_code'      => 'nullable|string|max:100',
            'product_type'      => 'nullable|string|max:100',
            'unit'              => 'nullable|string|max:50',
            'purchase_unit'     => 'nullable|string|max:50',
            'sales_unit'        => 'nullable|string|max:50',
            'conversion_factor' => 'nullable|numeric|min:0.0001',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'purchase_rate'     => 'nullable|numeric|min:0',
            'sales_rate'        => 'nullable|numeric|min:0',
            'wholesale_rate'    => 'nullable|numeric|min:0',
            'mrp'               => 'nullable|numeric|min:0',
            'hsn_code'          => 'nullable|string|max:20',
            'gst_rate'          => 'required|numeric|min:0|max:100',
            'barcode'           => 'nullable|string|max:100',
            'minimum_stock'     => 'nullable|numeric|min:0',
            'maximum_stock'     => 'nullable|numeric|min:0',
            'reorder_level'     => 'nullable|numeric|min:0',
            'warehouse_id'      => 'nullable|exists:warehouses,id',
        ]);

        $validated['sales_rate'] = !empty($validated['sales_rate']) ? $validated['sales_rate'] : $validated['price'];
        $product->update($validated);

        return new ProductResource($product->load('category'));
    }

    public function destroy(Product $product)
    {
        if ($product->invoiceItems()->exists() || $product->jobWorkOrderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete product because existing invoices or job work orders are linked to this product.',
            ], 422);
        }

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }
}
