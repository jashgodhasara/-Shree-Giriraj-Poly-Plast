<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'hsn_code'       => 'nullable|string|max:20',
            'gst_rate'       => 'required|numeric|min:0|max:100',
            'stock_quantity' => 'nullable|numeric|min:0',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $validated['image'] = 'uploads/products/' . $filename;
        }

        Product::create($validated);

        return response()->json(['success' => true, 'message' => 'Product added successfully.']);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'hsn_code'       => 'nullable|string|max:20',
            'gst_rate'       => 'required|numeric|min:0|max:100',
            'stock_quantity' => 'nullable|numeric|min:0',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:4096',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
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
        if ($product->image && file_exists(public_path($product->image))) {
            @unlink(public_path($product->image));
        }
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }
}
