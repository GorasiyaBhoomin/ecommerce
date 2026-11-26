<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        if ($search === '') {
            $search = $request->string('q')->trim()->toString();
        }

        $statusFilter = $request->string('status')->trim()->toString();
        $parentIdFilter = $request->string('parent_id')->trim()->toString();

        $query = Category::with('parent');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('categories.name', 'like', '%'.$search.'%')
                    ->orWhere('categories.slug', 'like', '%'.$search.'%')
                    ->orWhere('categories.description', 'like', '%'.$search.'%')
                    ->orWhereHas('parent', function ($pQ) use ($search) {
                        $pQ->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($statusFilter === 'active') {
            $query->where('categories.is_active', true);
        } elseif ($statusFilter === 'hidden') {
            $query->where('categories.is_active', false);
        }

        if ($parentIdFilter === 'root') {
            $query->whereNull('categories.parent_id');
        } elseif (is_numeric($parentIdFilter)) {
            $query->where('categories.parent_id', (int) $parentIdFilter);
        }

        // Sorting
        $allowedSorts = ['name', 'slug', 'display_order', 'is_active', 'created_at'];
        $sortBy = $request->string('sort_by')->trim()->toString();
        $sortDir = strtolower($request->string('sort_dir', 'asc')->toString()) === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy('categories.'.$sortBy, $sortDir);
        } else {
            $query->orderBy('categories.display_order')->orderBy('categories.name');
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $categories = $query->paginate($perPage)->withQueryString();
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.admin-categories', [
            'categories' => $categories,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'parentIdFilter' => $parentIdFilter,
            'parentCategories' => $parentCategories,
            'perPage' => $perPage,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
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

        $category = Category::create([
            'name' => $categoryData['name'],
            'slug' => $categoryData['slug'],
            'parent_id' => $categoryData['parent_id'] ?? null,
            'display_order' => $categoryData['display_order'] ?? 1,
            'description' => $categoryData['description'] ?? null,
            'actual_price' => $actualPrice,
            'discounted_price' => $discountedPrice,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category,
            ]);
        }

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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category,
            ]);
        }

        return redirect('/admincategories')->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        $category->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Category \"{$name}\" deleted successfully.",
            ]);
        }

        return redirect('/admincategories')->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category->is_active = ! $category->is_active;
        $category->save();

        $statusText = $category->is_active ? 'Active' : 'Inactive';

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $category->is_active,
                'status_text' => $statusText,
                'message' => "Category \"{$category->name}\" status updated to {$statusText}.",
            ]);
        }

        return back()->with('success', "Category \"{$category->name}\" status updated to {$statusText}.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:categories,id',
        ]);

        $ids = $request->input('ids', []);
        $count = Category::whereIn('id', $ids)->delete();

        $message = "Deleted {$count} category/categories successfully.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $count,
                'message' => $message,
            ]);
        }

        return redirect('/admincategories')->with('success', $message);
    }
}
