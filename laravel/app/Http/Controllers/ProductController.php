<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\JobWorkCalculationService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::latest()->get();
        if ($request->wantsJson()) {
            return response()->json($products);
        }
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function store(Request $request, JobWorkCalculationService $calcService)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'sku'                 => 'nullable|string|max:100',
            'unit_type'           => 'nullable|string|max:50',
            'weight_per_piece'    => 'nullable|numeric|min:0',
            'weight_unit'         => 'nullable|in:Gram,KG,Milligram,Ton',
            'wastage_percentage'  => 'nullable|numeric|min:0|max:100',
            'fixed_wastage'       => 'nullable|numeric|min:0',
            'job_work_applicable' => 'nullable|boolean',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric|min:0',
            'hsn_code'            => 'nullable|string|max:20',
            'gst_rate'            => 'required|numeric|min:0|max:100',
            'stock_quantity'      => 'nullable|numeric|min:0',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        $validated['job_work_applicable'] = $request->boolean('job_work_applicable', true);
        $validated['is_active']           = $request->boolean('is_active', true);
        $validated['unit_type']           = $validated['unit_type'] ?: 'PCS';
        $validated['weight_unit']         = $validated['weight_unit'] ?: 'Gram';
        $validated['weight_per_piece']    = (float) ($validated['weight_per_piece'] ?? 0);
        $validated['weight_in_grams']     = $calcService->convertToGrams($validated['weight_per_piece'], $validated['weight_unit']);

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

        Product::create($validated);

        return response()->json(['success' => true, 'message' => 'Product added successfully.']);
    }

    public function update(Request $request, Product $product, JobWorkCalculationService $calcService)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'sku'                 => 'required|string|max:100|unique:products,sku,' . $product->id,
            'unit_type'           => 'nullable|string|max:50',
            'weight_per_piece'    => 'nullable|numeric|min:0',
            'weight_unit'         => 'nullable|in:Gram,KG,Milligram,Ton',
            'wastage_percentage'  => 'nullable|numeric|min:0|max:100',
            'fixed_wastage'       => 'nullable|numeric|min:0',
            'job_work_applicable' => 'nullable|boolean',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric|min:0',
            'hsn_code'            => 'nullable|string|max:20',
            'gst_rate'            => 'required|numeric|min:0|max:100',
            'stock_quantity'      => 'nullable|numeric|min:0',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        $validated['job_work_applicable'] = $request->boolean('job_work_applicable', true);
        $validated['is_active']           = $request->boolean('is_active', true);
        $validated['unit_type']           = $validated['unit_type'] ?: 'PCS';
        $validated['weight_unit']         = $validated['weight_unit'] ?: 'Gram';
        $validated['weight_per_piece']    = (float) ($validated['weight_per_piece'] ?? 0);
        $validated['weight_in_grams']     = $calcService->convertToGrams($validated['weight_per_piece'], $validated['weight_unit']);

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

        return response()->json(['success' => true, 'message' => 'Product updated successfully.']);
    }

    public function destroy(Product $product)
    {
        if ($product->invoiceItems()->exists() || $product->productionLogs()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete product because existing invoices or production logs are linked to this product.',
            ], 422);
        }

        if ($product->image && file_exists(public_path($product->image))) {
            @unlink(public_path($product->image));
        }
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }
}
