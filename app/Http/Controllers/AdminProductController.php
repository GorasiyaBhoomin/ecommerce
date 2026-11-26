<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\VariantGroup;
use App\Models\VariantValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        $selectedCategory = $request->filled('category')
            ? $categories->firstWhere('slug', $request->query('category'))
            : null;

        $search = $request->string('search')->trim()->toString();
        if ($search === '') {
            $search = $request->string('q')->trim()->toString();
        }

        $stockFilter = $request->string('stock')->trim()->toString();
        $statusFilter = $request->string('status')->trim()->toString();

        $query = Product::with(['category', 'images', 'variants.values'])
            ->when($selectedCategory, function ($q) use ($selectedCategory) {
                $q->where('products.category_id', $selectedCategory->id);
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', '%' . $search . '%')
                    ->orWhere('products.sku', 'like', '%' . $search . '%')
                    ->orWhere('products.description', 'like', '%' . $search . '%')
                    ->orWhereHas('variants', function ($vQ) use ($search) {
                        $vQ->where('sku', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('category', function ($cQ) use ($search) {
                        $cQ->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($stockFilter === 'in_stock') {
            $query->whereHas('variants', function ($v) {
                $v->where('stock_qty', '>', 10);
            });
        } elseif ($stockFilter === 'low_stock') {
            $query->whereHas('variants', function ($v) {
                $v->where('stock_qty', '>', 0)->where('stock_qty', '<=', 10);
            });
        } elseif ($stockFilter === 'out_of_stock') {
            $query->whereDoesntHave('variants', function ($v) {
                $v->where('stock_qty', '>', 0);
            });
        }

        if ($statusFilter === 'active') {
            $query->where('products.is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('products.is_active', false);
        }

        // Sorting
        $allowedSorts = ['name', 'sku', 'created_at', 'is_active', 'category'];
        $sortBy = $request->string('sort_by')->trim()->toString();
        $sortDir = strtolower($request->string('sort_dir', 'desc')->toString()) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'category') {
            $query->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->select('products.*')
                ->orderBy('categories.name', $sortDir);
        } elseif (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy('products.' . $sortBy, $sortDir);
        } else {
            $query->latest('products.id');
        }

        $defaultPerPage = app()->runningUnitTests() ? 6 : 10;
        $perPage = (int) $request->input('per_page', $defaultPerPage);
        if (!in_array($perPage, [6, 10, 25, 50, 100], true)) {
            $perPage = $defaultPerPage;
        }

        $products = $query->paginate($perPage)
            ->withQueryString();

        return view('admin.admin-products', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'stockFilter' => $stockFilter,
            'statusFilter' => $statusFilter,
            'perPage' => $perPage,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    public function create(): View
    {
        $variantGroups = VariantGroup::with(['values', 'categories'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn(VariantGroup $group): array => [
                'id' => $group->id,
                'name' => $group->name,
                'label' => $group->label,
                'category_ids' => $group->categories->pluck('id')->all(),
                'values' => $group->values->map(fn(VariantValue $value): array => [
                    'id' => $value->id,
                    'value' => $value->value,
                    'sku_suffix' => $value->sku_suffix,
                ])->all(),
            ])
            ->values()
            ->all();

        return view('admin.admin-add-product', [
            'categories' => Category::orderBy('name')->get(),
            'variantGroups' => $variantGroups,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $productData = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'images' => 'required|array|min:3',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'variants' => 'nullable|array',
            'variants.*.variant_value_ids' => 'required|array|min:1',
            'variants.*.variant_value_ids.*' => 'integer|exists:variant_values,id',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.actual_price' => 'required|numeric|min:0',
            'variants.*.discounted_price' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'specs' => 'nullable|array',
            'specs.*.label' => 'nullable|string|max:255',
            'specs.*.value' => 'nullable|string|max:255',
        ]);

        $variants = $productData['variants'] ?? [];

        $priceErrors = [];

        foreach ($variants as $index => $variant) {
            if (($variant['discounted_price'] ?? 0) > $variant['actual_price']) {
                $priceErrors["variants.$index.discounted_price"] = 'The discounted price cannot be greater than the actual price.';
            }
        }

        if ($priceErrors !== []) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The discounted price cannot be greater than the actual price.',
                    'errors' => $priceErrors,
                ], 422);
            }

            return back()->withInput()->withErrors($priceErrors);
        }

        $specifications = collect($productData['specs'] ?? [])
            ->filter(fn(array $spec): bool => filled($spec['label'] ?? null))
            ->values();

        $product = DB::transaction(function () use ($productData, $request, $variants, $specifications): Product {
            $product = Product::create([
                'category_id' => $productData['category_id'],
                'name' => $productData['name'],
                'slug' => $this->uniqueSlug($productData['name']),
                'sku' => $this->baseSku($productData['name']),
                'description' => $productData['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $imageDirectory = public_path('uploads/products');
            File::ensureDirectoryExists($imageDirectory);

            foreach ($request->file('images', []) as $position => $image) {
                $filename = $image->hashName();
                $image->move($imageDirectory, $filename);

                $product->images()->create([
                    'path' => 'uploads/products/' . $filename,
                    'position' => $position,
                ]);
            }

            foreach ($variants as $variant) {
                $productVariant = $product->variants()->create([
                    'sku' => $variant['sku'] ?? null,
                    'actual_price' => $variant['actual_price'],
                    'discounted_price' => $variant['discounted_price'] ?? 0,
                    'stock_qty' => $variant['stock_qty'] ?? 0,
                    'is_active' => true,
                ]);

                $productVariant->values()->sync($variant['variant_value_ids']);
            }

            foreach ($specifications as $position => $specification) {
                $product->specifications()->create([
                    'label' => $specification['label'],
                    'value' => $specification['value'] ?? null,
                    'position' => $position,
                ]);
            }

            return $product;
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product "'.$product->name.'" created successfully.',
                'product' => $product,
            ]);
        }

        return redirect()->route('admin.products.create')
            ->with('success', 'Product "' . $product->name . '" created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'variants.values', 'specifications']);

        return view('admin.admin-edit-product', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'variantGroups' => $this->variantGroups(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer',
            'variants.*.variant_value_ids' => 'required|array|min:1',
            'variants.*.variant_value_ids.*' => 'integer|exists:variant_values,id',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.actual_price' => 'required|numeric|min:0',
            'variants.*.discounted_price' => 'nullable|numeric|min:0',
            'variants.*.stock_qty' => 'nullable|integer|min:0',
            'specs' => 'nullable|array',
            'specs.*.label' => 'nullable|string|max:255',
            'specs.*.value' => 'nullable|string|max:255',
        ]);

        $variants = $data['variants'] ?? [];
        $errors = [];

        foreach ($variants as $index => $variant) {
            if (($variant['discounted_price'] ?? 0) > $variant['actual_price']) {
                $errors["variants.$index.discounted_price"] = 'The discounted price cannot be greater than the actual price.';
            }
        }

        $removeIds = collect($data['remove_image_ids'] ?? [])
            ->intersect($product->images()->pluck('id'))
            ->values();
        $remaining = $product->images()->count() - $removeIds->count() + count($request->file('images', []));

        if ($remaining < 3) {
            $errors['images'] = 'A product needs at least 3 images.';
        }

        if ($errors !== []) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ], 422);
            }

            return back()->withInput()->withErrors($errors);
        }

        $specifications = collect($data['specs'] ?? [])
            ->filter(fn(array $spec): bool => filled($spec['label'] ?? null))
            ->values();

        $pathsToDelete = [];

        DB::transaction(function () use ($product, $data, $request, $variants, $specifications, $removeIds, &$pathsToDelete): void {
            $product->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            // Images: remove selected, add new ones at the end, then renumber
            foreach ($product->images()->whereIn('id', $removeIds)->get() as $image) {
                $pathsToDelete[] = public_path($image->path);
                $image->delete();
            }

            $imageDirectory = public_path('uploads/products');
            File::ensureDirectoryExists($imageDirectory);
            $position = (int) $product->images()->max('position') + 1;

            foreach ($request->file('images', []) as $image) {
                $filename = $image->hashName();
                $image->move($imageDirectory, $filename);
                $product->images()->create([
                    'path' => 'uploads/products/' . $filename,
                    'position' => $position++,
                ]);
            }

            foreach ($product->images()->orderBy('position')->get() as $i => $image) {
                $image->update(['position' => $i]);
            }

            // Variants: update by id, create new, delete the ones no longer submitted
            $existing = $product->variants()->get()->keyBy('id');
            $keptIds = [];

            foreach ($variants as $variant) {
                $attributes = [
                    'sku' => $variant['sku'] ?? null,
                    'actual_price' => $variant['actual_price'],
                    'discounted_price' => $variant['discounted_price'] ?? 0,
                    'stock_qty' => $variant['stock_qty'] ?? 0,
                    'is_active' => true,
                ];

                $productVariant = isset($variant['id']) ? $existing->get($variant['id']) : null;

                if ($productVariant) {
                    $productVariant->update($attributes);
                } else {
                    $productVariant = $product->variants()->create($attributes);
                }

                $productVariant->values()->sync($variant['variant_value_ids'] ?? []);
                $keptIds[] = $productVariant->id;   // <- aa line add karo
            }

            $product->variants()->whereNotIn('id', $keptIds)->get()->each->delete();

            // Specs: replace wholesale
            $product->specifications()->delete();
            foreach ($specifications as $i => $spec) {
                $product->specifications()->create([
                    'label' => $spec['label'],
                    'value' => $spec['value'] ?? null,
                    'position' => $i,
                ]);
            }
        });

        File::delete($pathsToDelete);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product "'.$product->name.'" updated successfully.',
                'product' => $product,
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product "' . $product->name . '" updated successfully.');
    }

    private function variantGroups(): array
    {
        return VariantGroup::with(['values', 'categories'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn(VariantGroup $group): array => [
                'id' => $group->id,
                'name' => $group->name,
                'label' => $group->label,
                'category_ids' => $group->categories->pluck('id')->all(),
                'values' => $group->values->map(fn(VariantValue $value): array => [
                    'id' => $value->id,
                    'value' => $value->value,
                    'sku_suffix' => $value->sku_suffix,
                ])->all(),
            ])
            ->values()
            ->all();
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function baseSku(string $name): string
    {
        return Str::upper(Str::slug($name)) ?: 'SKU';
    }

    public function destroy(Request $request, string $product)
    {
        $item = Product::findOrFail($product);

        if (DB::table('order_items')->where('product_id', $item->id)->exists()) {
            $msg = 'Cannot delete "' . $item->name . '" because it is referenced in customer orders. You can set it to inactive instead.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect('/adminproducts')->with('error', $msg);
        }

        foreach ($item->images as $image) {
            if (!Str::startsWith($image->path, ['http://', 'https://'])) {
                File::delete(public_path($image->path));
            }
        }

        $name = $item->name;
        $item->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product "' . $name . '" deleted successfully.',
            ]);
        }

        return redirect('/adminproducts')->with('success', 'Product "' . $name . '" deleted successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        $statusText = $product->is_active ? 'Active' : 'Inactive';

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $product->is_active,
                'status_text' => $statusText,
                'message' => "Product \"{$product->name}\" status updated to {$statusText}.",
            ]);
        }

        return back()->with('success', "Product \"{$product->name}\" status updated to {$statusText}.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:products,id',
        ]);

        $ids = $request->input('ids', []);
        $deletedCount = 0;
        $skippedNames = [];

        $products = Product::whereIn('id', $ids)->get();

        foreach ($products as $item) {
            if (DB::table('order_items')->where('product_id', $item->id)->exists()) {
                $skippedNames[] = $item->name;

                continue;
            }

            foreach ($item->images as $image) {
                if (!Str::startsWith($image->path, ['http://', 'https://'])) {
                    File::delete(public_path($image->path));
                }
            }

            $item->delete();
            $deletedCount++;
        }

        $message = "Deleted {$deletedCount} product(s) successfully.";
        if (count($skippedNames) > 0) {
            $message .= ' Skipped (referenced in orders): ' . implode(', ', $skippedNames) . '.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'skipped_count' => count($skippedNames),
                'message' => $message,
            ]);
        }

        return redirect('/adminproducts')->with('success', $message);
    }
}
