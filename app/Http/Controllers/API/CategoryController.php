<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;

    /**
     * Get list of all categories with active product count.
     */
    public function index(Request $request): JsonResponse
    {
        $onlyActive = ! $request->boolean('all');

        $query = Category::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('display_order')
            ->orderBy('name');

        if ($request->boolean('tree')) {
            $categories = $query->whereNull('parent_id')
                ->with(['parent'])
                ->get();
        } else {
            $categories = $query->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ], 200);
    }

    /**
     * Get single category by ID or slug.
     */
    public function show(string|int $idOrSlug): JsonResponse
    {
        $category = Category::query()
            ->where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->first();

        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Category retrieved successfully',
            'data' => [
                'category' => $category,
            ],
        ], 200);
    }

    /**
     * Get category-wise active products.
     */
    public function products(Request $request, string|int $idOrSlug): JsonResponse
    {
        $category = Category::query()
            ->where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->first();

        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $query = Product::query()
            ->with(['category', 'images', 'variants.values.group'])
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('name');

        $products = $query->get()
            ->map(fn (Product $product): array => $this->transformCatalogCard($product))
            ->all();

        return response()->json([
            'status' => true,
            'message' => 'Category products retrieved successfully',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ],
                'total_products' => count($products),
                'products' => $products,
            ],
        ], 200);
    }

    /**
     * Transform a product model into the standard storefront catalog card.
     *
     * @return array<string, mixed>
     */
    private function transformCatalogCard(Product $product): array
    {
        $variants = $product->variants->where('is_active', true);
        $stock = (int) $variants->sum('stock_qty');

        if ($stock <= 0) {
            $stockState = 'out_of_stock';
            $stockLabel = 'Out of stock';
        } elseif ($stock <= self::LOW_STOCK_THRESHOLD) {
            $stockState = 'low_stock';
            $stockLabel = 'Low stock ('.$stock.' left)';
        } else {
            $stockState = 'in_stock';
            $stockLabel = 'In stock';
        }

        $prices = $variants->map(function (ProductVariant $variant): float {
            $discounted = (float) $variant->discounted_price;

            return $discounted > 0 ? $discounted : (float) $variant->actual_price;
        });

        $firstImage = $product->images->first();
        $imageUrl = null;
        if ($firstImage !== null) {
            $imageUrl = Str::startsWith($firstImage->path, ['http://', 'https://'])
                ? $firstImage->path
                : asset($firstImage->path);
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'category' => $product->category->name ?? 'Uncategorised',
            'category_id' => $product->category_id,
            'image' => $imageUrl,
            'description' => $product->description,
            'stock_qty' => $stock,
            'stock_state' => $stockState,
            'stock_label' => $stockLabel,
            'variant_summary' => $this->variantSummary($variants),
            'from_price' => $prices->isNotEmpty() ? $prices->min() : null,
            'is_active' => (bool) $product->is_active,
        ];
    }

    /**
     * Describe variant summary.
     */
    private function variantSummary($variants): string
    {
        $groupNames = $variants
            ->flatMap(fn (ProductVariant $variant) => $variant->values->pluck('group.name'))
            ->filter()
            ->unique()
            ->values();

        if ($groupNames->isEmpty()) {
            return 'No variants';
        }

        return $variants->count().' '.Str::plural('variant', $variants->count()).' • '.$groupNames->implode(' • ');
    }
}
