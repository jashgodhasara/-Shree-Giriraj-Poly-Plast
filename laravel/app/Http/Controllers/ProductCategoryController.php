<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = ProductCategory::withCount('products')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $categories = $query->paginate(20)->appends($request->query());

        if ($request->wantsJson()) {
            return response()->json($categories);
        }

        return view('categories.index', compact('categories', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:product_categories,code',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:active,inactive',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::slug($validated['name']));
            // ensure unique
            $count = ProductCategory::where('code', 'LIKE', $validated['code'] . '%')->count();
            if ($count > 0) {
                $validated['code'] .= '-' . ($count + 1);
            }
        }

        $validated['status'] = $validated['status'] ?? 'active';

        $category = ProductCategory::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Category created successfully.', 'category' => $category]);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:product_categories,code,' . $category->id,
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $category->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Category updated successfully.', 'category' => $category]);
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, ProductCategory $category)
    {
        if ($category->products()->count() > 0) {
            $msg = "Cannot delete category '{$category->name}' because {$category->products()->count()} products are assigned to it. Please reassign products first.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $category->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
