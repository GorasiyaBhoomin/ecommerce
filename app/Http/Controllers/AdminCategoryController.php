<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        return view('admin.admin-categories', [
            'categories' => Category::with('parent')->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.admin-add-category', [
            'parentCategories' => Category::whereNull('parent_id')->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $categoryData = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'display_order' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'actual_price' => 'nullable|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $actualPrice = $categoryData['actual_price'] ?? null;
        $discountedPrice = $categoryData['discounted_price'] ?? null;

        if ($actualPrice !== null && $discountedPrice !== null && $discountedPrice > $actualPrice) {
            return back()->withInput()->withErrors([
                'discounted_price' => 'The discounted price cannot be greater than the actual price.',
            ]);
        }

        Category::create([
            'name' => $categoryData['name'],
            'slug' => $categoryData['slug'],
            'parent_id' => $categoryData['parent_id'] ?? null,
            'display_order' => $categoryData['display_order'] ?? 1,
            'description' => $categoryData['description'] ?? null,
            'actual_price' => $actualPrice,
            'discounted_price' => $discountedPrice,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect('/admincategories')->with('success', 'Category created successfully.');
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);

        return view('admin.admin-edit-category', [
            'category' => $category,
            'parentCategories' => Category::whereNull('parent_id')
                ->where('id', '!=', $category->id)
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $categoryData = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,'.$category->id,
            'parent_id' => 'nullable|integer|exists:categories,id|not_in:'.$category->id,
            'display_order' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'actual_price' => 'nullable|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $actualPrice = $categoryData['actual_price'] ?? null;
        $discountedPrice = $categoryData['discounted_price'] ?? null;

        if ($actualPrice !== null && $discountedPrice !== null && $discountedPrice > $actualPrice) {
            return back()->withInput()->withErrors([
                'discounted_price' => 'The discounted price cannot be greater than the actual price.',
            ]);
        }

        $category->update([
            'name' => $categoryData['name'],
            'slug' => $categoryData['slug'],
            'parent_id' => $categoryData['parent_id'] ?? null,
            'display_order' => $categoryData['display_order'] ?? 1,
            'description' => $categoryData['description'] ?? null,
            'actual_price' => $actualPrice,
            'discounted_price' => $discountedPrice,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect('/admincategories')->with('success', 'Category updated successfully.');
    }

    public function destroy(string $id)
    {
        Category::findOrFail($id)->delete();

        return redirect('/admincategories')->with('success', 'Category deleted successfully.');
    }
}
