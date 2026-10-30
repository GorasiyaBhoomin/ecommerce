<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;

    private const COLOR_HEX_MAP = [
        'black' => '#0F172A',
        'white' => '#FFFFFF',
        'silver' => '#CBD5E1',
        'grey' => '#94A3B8',
        'gray' => '#94A3B8',
        'beige' => '#E7E5E4',
        'red' => '#DC2626',
        'maroon' => '#7F1D1D',
        'orange' => '#F97316',
        'yellow' => '#EAB308',
        'gold' => '#D4AF37',
        'green' => '#16A34A',
        'olive' => '#4D7C0F',
        'teal' => '#0F766E',
        'blue' => '#2563EB',
        'navy' => '#1E3A8A',
        'purple' => '#7C3AED',
        'violet' => '#7C3AED',
        'pink' => '#DB2777',
        'brown' => '#78350F',
    ];

    /**
     * Get products list with comprehensive filtering (category, search, price range, stock, sort).
     */
    public function index(Request $request): JsonResponse
    {
        $categoryParam = $request->input('category');
        $search = $request->string('search')->trim()->toString();
        if ($search === '') {
            $search = $request->string('q')->trim()->toString();
        }

        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $inStockOnly = $request->boolean('in_stock');
        $sortBy = $request->string('sort_by', 'name_asc')->toString();

        $query = Product::query()
            ->with(['category', 'images', 'variants.values.group'])
            ->where('is_active', true);

        // Filter by category slug or ID
        if (! empty($categoryParam)) {
            $query->where(function ($q) use ($categoryParam) {
                if (is_numeric($categoryParam)) {
                    $q->where('category_id', $categoryParam);
                } else {
                    $q->whereHas('category', fn ($cat) => $cat->where('slug', $categoryParam));
                }
            });
        }

        // Search in product name or description or sku
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%');
            });
        }

        // Filter by variant price range if supplied
        if ($minPrice !== null || $maxPrice !== null) {
            $query->whereHas('variants', function ($variantQuery) use ($minPrice, $maxPrice) {
                $variantQuery->where('is_active', true);
                if ($minPrice !== null) {
                    $variantQuery->where(function ($p) use ($minPrice) {
                        $p->where(function ($sub) use ($minPrice) {
                            $sub->where('discounted_price', '>', 0)
                                ->where('discounted_price', '>=', $minPrice);
                        })->orWhere(function ($sub) use ($minPrice) {
                            $sub->where(function ($z) {
                                $z->whereNull('discounted_price')->orWhere('discounted_price', '<=', 0);
                            })->where('actual_price', '>=', $minPrice);
                        });
                    });
                }
                if ($maxPrice !== null) {
                    $variantQuery->where(function ($p) use ($maxPrice) {
                        $p->where(function ($sub) use ($maxPrice) {
                            $sub->where('discounted_price', '>', 0)
                                ->where('discounted_price', '<=', $maxPrice);
                        })->orWhere(function ($sub) use ($maxPrice) {
                            $sub->where(function ($z) {
                                $z->whereNull('discounted_price')->orWhere('discounted_price', '<=', 0);
                            })->where('actual_price', '<=', $maxPrice);
                        });
                    });
                }
            });
        }

        // Filter by in-stock products
        if ($inStockOnly) {
            $query->whereHas('variants', function ($vQuery) {
                $vQuery->where('is_active', true)->where('stock_qty', '>', 0);
            });
        }

        // Execute query and transform
        $productsCollection = $query->get()->map(fn (Product $product): array => $this->catalogCard($product));

        // Sort collection
        $productsCollection = match ($sortBy) {
            'price_asc', 'price_low_high' => $productsCollection->sortBy('from_price')->values(),
            'price_desc', 'price_high_low' => $productsCollection->sortByDesc('from_price')->values(),
            'name_desc' => $productsCollection->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'newest', 'latest' => $productsCollection->sortByDesc('id')->values(),
            default => $productsCollection->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
        };

        $totalCount = $productsCollection->count();

        // Optional pagination
        $perPageParam = $request->input('per_page');
        if ($perPageParam && $perPageParam !== 'all') {
            $perPage = max(1, (int) $perPageParam);
            $page = max(1, (int) $request->input('page', 1));
            $paginatedItems = $productsCollection->forPage($page, $perPage)->values();

            $data = [
                'items' => $paginatedItems,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalCount,
                    'last_page' => (int) ceil($totalCount / $perPage),
                ],
            ];
        } else {
            $data = [
                'items' => $productsCollection,
                'total' => $totalCount,
            ];
        }

        // Available categories for filter tabs
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $data,
            'filters' => [
                'applied' => [
                    'category' => $categoryParam,
                    'search' => $search !== '' ? $search : null,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'in_stock' => $inStockOnly,
                    'sort_by' => $sortBy,
                ],
                'categories' => $categories,
            ],
        ], 200);
    }

    /**
     * Get featured product card.
     */
    public function featured(): JsonResponse
    {
        $featuredProduct = Product::query()
            ->with(['category', 'images', 'variants.values.group'])
            ->where('is_active', true)
            ->orderBy('name')
            ->first();

        return response()->json([
            'status' => true,
            'message' => $featuredProduct ? 'Featured product retrieved' : 'No featured product found',
            'data' => $featuredProduct ? $this->catalogCard($featuredProduct) : null,
        ], 200);
    }

    /**
     * Get single product detail including variant matrix and option groups.
     */
    public function show(string|int $idOrSlug): JsonResponse
    {
        $product = $this->findActiveProduct($idOrSlug);

        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $variants = $this->variantMatrix($product);

        $defaultKey = array_key_first(array_filter(
            $variants,
            fn (array $variant): bool => $variant['stock_qty'] > 0,
        )) ?? array_key_first($variants);

        $images = $product->images->map(function ($image): array {
            return [
                'id' => $image->id,
                'position' => (int) $image->position,
                'url' => Str::startsWith($image->path, ['http://', 'https://']) ? $image->path : asset($image->path),
            ];
        })->values()->all();

        $specifications = $product->specifications->map(function ($spec): array {
            return [
                'id' => $spec->id,
                'label' => $spec->label,
                'value' => $spec->value,
                'position' => (int) $spec->position,
            ];
        })->values()->all();

        $totalStock = (int) $product->variants->where('is_active', true)->sum('stock_qty');

        return response()->json([
            'status' => true,
            'message' => 'Product retrieved successfully',
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'description' => $product->description,
                    'total_stock' => $totalStock,
                    'is_in_stock' => $totalStock > 0,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                    'is_active' => (bool) $product->is_active,
                ],
                'images' => $images,
                'specifications' => $specifications,
                'groups' => $this->variantGroups($product),
                'variants' => $variants,
                'default_variant' => $defaultKey !== null ? $variants[$defaultKey] : null,
                'low_stock_threshold' => self::LOW_STOCK_THRESHOLD,
            ],
        ], 200);
    }

    /**
     * Get product images.
     */
    public function images(string|int $idOrSlug): JsonResponse
    {
        $product = $this->findActiveProduct($idOrSlug);

        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $images = $product->images->map(fn ($img) => [
            'id' => $img->id,
            'position' => (int) $img->position,
            'url' => Str::startsWith($img->path, ['http://', 'https://']) ? $img->path : asset($img->path),
        ])->values()->all();

        return response()->json([
            'status' => true,
            'message' => 'Product images retrieved successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'images' => $images,
            ],
        ], 200);
    }

    /**
     * Get product specifications.
     */
    public function specifications(string|int $idOrSlug): JsonResponse
    {
        $product = $this->findActiveProduct($idOrSlug);

        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $specs = $product->specifications->map(fn ($spec) => [
            'id' => $spec->id,
            'label' => $spec->label,
            'value' => $spec->value,
            'position' => (int) $spec->position,
        ])->values()->all();

        return response()->json([
            'status' => true,
            'message' => 'Product specifications retrieved successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'specifications' => $specs,
            ],
        ], 200);
    }

    /**
     * Get product variants (RAM, Storage, Color, prices, discounts, stock).
     */
    public function variants(string|int $idOrSlug): JsonResponse
    {
        $product = $this->findActiveProduct($idOrSlug);

        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $variants = $product->variants->where('is_active', true)->map(function (ProductVariant $v): array {
            $actual = (float) $v->actual_price;
            $discounted = (float) $v->discounted_price;
            $effective = $discounted > 0 ? $discounted : $actual;
            $discountAmount = max(0, $actual - $effective);
            $discountPercent = $actual > 0 ? round(($discountAmount / $actual) * 100) : 0;

            $options = [];
            foreach ($v->values as $val) {
                $groupName = $val->group->name ?? 'Option';
                $options[$groupName] = $val->value;
            }

            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'actual_price' => $actual,
                'discounted_price' => $discounted,
                'effective_price' => $effective,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercent,
                'stock_qty' => (int) $v->stock_qty,
                'is_in_stock' => $v->stock_qty > 0,
                'is_low_stock' => $v->stock_qty > 0 && $v->stock_qty <= self::LOW_STOCK_THRESHOLD,
                'options' => $options,
                'values' => $v->values->map(fn ($val) => [
                    'group_id' => $val->group_id,
                    'group_name' => $val->group->name ?? null,
                    'value_id' => $val->id,
                    'value' => $val->value,
                ])->values()->all(),
            ];
        })->values()->all();

        return response()->json([
            'status' => true,
            'message' => 'Product variants retrieved successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variants' => $variants,
                'variant_groups' => $this->variantGroups($product),
            ],
        ], 200);
    }

    /**
     * Find active product by ID or Slug.
     */
    private function findActiveProduct(string|int $idOrSlug): ?Product
    {
        return Product::query()
            ->with(['category', 'images', 'specifications', 'variants.values.group'])
            ->where('is_active', true)
            ->where(function ($q) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $q->where('id', $idOrSlug)->orWhere('slug', (string) $idOrSlug);
                } else {
                    $q->where('slug', $idOrSlug);
                }
            })
            ->first();
    }

    /**
     * Build catalog card representation of a product.
     *
     * @return array<string, mixed>
     */
    private function catalogCard(Product $product): array
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

        $prices = $variants->map(fn (ProductVariant $variant): float => $this->effectivePrice($variant));

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
     * Build variant option groups with color hex mapping.
     *
     * @return array<int, array<string, mixed>>
     */
    private function variantGroups(Product $product): array
    {
        $groups = [];

        foreach ($product->variants as $variant) {
            if (! $variant->is_active) {
                continue;
            }

            foreach ($variant->values as $value) {
                if ($value->group === null) {
                    continue;
                }

                $group = $value->group;

                $groups[$group->id] ??= [
                    'id' => $group->id,
                    'name' => $group->name,
                    'is_color' => (bool) preg_match('/colou?r/i', $group->name),
                    'values' => [],
                ];

                $groups[$group->id]['values'][$value->id] = [
                    'id' => $value->id,
                    'value' => $value->value,
                    'hex' => $this->colorHex($value->value),
                ];
            }
        }

        ksort($groups);

        return array_values(array_map(function (array $group): array {
            $group['values'] = array_values($group['values']);

            return $group;
        }, $groups));
    }

    /**
     * Build the variant matrix with selectable keys and pricing.
     *
     * @return array<string, array<string, mixed>>
     */
    private function variantMatrix(Product $product): array
    {
        $matrix = [];

        foreach ($product->variants as $variant) {
            if (! $variant->is_active) {
                continue;
            }

            $valueIds = $variant->values
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values()
                ->all();

            $key = 'v'.implode('-', $valueIds);

            $actual = (float) $variant->actual_price;
            $discounted = (float) $variant->discounted_price;
            $effective = $this->effectivePrice($variant);
            $discountAmount = max(0, $actual - $effective);
            $discountPercentage = $actual > 0 ? round(($discountAmount / $actual) * 100) : 0;

            $matrix[$key] = [
                'id' => $variant->id,
                'key' => $key,
                'sku' => $variant->sku,
                'actual_price' => $actual,
                'discounted_price' => $discounted,
                'price' => $effective,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'stock_qty' => (int) $variant->stock_qty,
                'is_low_stock' => $variant->stock_qty > 0 && $variant->stock_qty <= self::LOW_STOCK_THRESHOLD,
                'is_out_of_stock' => $variant->stock_qty <= 0,
                'value_ids' => $valueIds,
                'option_values' => $variant->values->map(fn ($v) => [
                    'id' => $v->id,
                    'value' => $v->value,
                    'group_id' => $v->group_id,
                    'group_name' => $v->group->name ?? null,
                ])->values()->all(),
            ];
        }

        return $matrix;
    }

    /**
     * Calculate effective price.
     */
    private function effectivePrice(ProductVariant $variant): float
    {
        $discounted = (float) $variant->discounted_price;

        return $discounted > 0 ? $discounted : (float) $variant->actual_price;
    }

    /**
     * Describe variant summary in one line.
     *
     * @param  Collection<int, ProductVariant>  $variants
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

    /**
     * Map color name to hex.
     */
    private function colorHex(string $value): string
    {
        return self::COLOR_HEX_MAP[mb_strtolower(trim($value))] ?? '#CBD5E1';
    }
}
