<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantGroup;
use App\Models\VariantValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminProductImportController extends Controller
{
    /**
     * When true, a variant option that does not exist yet (e.g. a new color) is
     * created automatically. When false, the row is skipped with an error.
     */
    private const AUTO_CREATE_VALUES = false;

    private const MIN_IMAGES = 3;

    /**
     * Display the product CSV import form with category selection.
     */
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();
        $selectedCategoryId = (int) $request->query('category_id', 0);

        return view('admin.admin-import-products', [
            'categories' => $categories,
            'selectedCategoryId' => $selectedCategoryId,
        ]);
    }


    /**
     * Import products category-wise from a CSV file.
     *
     * - Rows with the same product name become variants of ONE product.
     * - Variant columns are headers that match a Variant Group name (e.g. color, storage).
     * - Product-level data (description, images, specifications) is read from the
     *   first row of each product that has it; later rows can leave them blank.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'default_images' => 'nullable|array',
            'default_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'category_id.required' => 'Please select a product category from the dropdown menu.',
            'category_id.exists' => 'The selected category does not exist.',
            'csv_file.required' => 'Please select a CSV file to upload.',
            'csv_file.mimes' => 'The uploaded file must be a valid CSV file.',
            'csv_file.max' => 'The CSV file size may not exceed 10MB.',
            'default_images.*.image' => 'Uploaded default images must be valid image files (jpg, jpeg, png, webp).',
        ]);

        $defaultImagePaths = [];
        if ($request->hasFile('default_images')) {
            $imageDirectory = public_path('uploads/products');
            File::ensureDirectoryExists($imageDirectory);

            foreach ($request->file('default_images') as $imgFile) {
                if ($imgFile->isValid()) {
                    $filename = $imgFile->hashName();
                    $imgFile->move($imageDirectory, $filename);
                    $defaultImagePaths[] = 'uploads/products/' . $filename;
                }
            }
        }

        $category = Category::findOrFail((int) $request->input('category_id'));
        $file = $request->file('csv_file');

        if (!$file || !$file->isValid()) {
            return back()->withInput()->with('error', 'The uploaded CSV file is invalid or corrupted.');
        }

        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return back()->withInput()->with('error', 'Unable to open the uploaded CSV file.');
        }

        // Read header row
        $rawHeader = fgetcsv($handle);

        if ($rawHeader === false || empty($rawHeader)) {
            fclose($handle);

            return back()->withInput()->with('error', 'The uploaded CSV file is empty or missing headers.');
        }

        // Strip UTF-8 BOM if present on first header column
        $rawHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $rawHeader[0]);

        $columnMap = [];
        foreach ($rawHeader as $index => $colName) {
            $columnMap[$this->normalizeHeader((string) $colName)] = $index;
        }

        $nameIndex = $this->resolveColumnIndex($columnMap, ['name', 'product_name', 'title']);
        $actualPriceIndex = $this->resolveColumnIndex($columnMap, ['actual_price', 'price', 'mrp', 'regular_price']);

        if ($nameIndex === null || $actualPriceIndex === null) {
            fclose($handle);

            return back()->withInput()->with(
                'error',
                'CSV file must contain at least "name" (or "product_name") and "actual_price" (or "price") columns.'
            );
        }

        $skuIndex = $this->resolveColumnIndex($columnMap, ['sku', 'product_sku', 'code']);
        $discountedPriceIndex = $this->resolveColumnIndex($columnMap, ['discounted_price', 'discount_price', 'sale_price', 'offer_price']);
        $stockIndex = $this->resolveColumnIndex($columnMap, ['stock_qty', 'stock', 'quantity', 'qty']);
        $descriptionIndex = $this->resolveColumnIndex($columnMap, ['description', 'desc', 'details']);
        $isActiveIndex = $this->resolveColumnIndex($columnMap, ['is_active', 'active', 'status']);
        $imageIndex = $this->resolveColumnIndex($columnMap, ['images', 'image', 'image_url', 'image_path']);
        $specIndex = $this->resolveColumnIndex($columnMap, ['specifications', 'specification', 'specs']);

        // Variant columns: any header that matches a Variant Group name or label
        $allGroups = VariantGroup::all();
        $groupMap = [];
        foreach ($allGroups as $group) {
            foreach ([$group->name, $group->label] as $groupLabel) {
                if (filled($groupLabel)) {
                    $groupMap[$this->normalizeHeader((string) $groupLabel)] = $group;
                }
            }
        }

        $reserved = array_filter(
            [$nameIndex, $actualPriceIndex, $skuIndex, $discountedPriceIndex, $stockIndex, $descriptionIndex, $isActiveIndex, $imageIndex, $specIndex],
            fn($i): bool => $i !== null
        );

        $variantColumns = []; // column index => VariantGroup
        foreach ($columnMap as $key => $index) {
            if (in_array($index, $reserved, true) || !isset($groupMap[$key])) {
                continue;
            }
            $variantColumns[$index] = $groupMap[$key];
        }

        if ($variantColumns === []) {
            fclose($handle);

            return back()->withInput()->with(
                'error',
                'No variant columns found. Add at least one column named exactly like a variant group ('
                . $allGroups->pluck('name')->implode(', ') . ').'
            );
        }

        // Lookup of existing variant options: group id => [normalized value => VariantValue]
        $optionMap = [];
        foreach (VariantValue::all() as $option) {
            $optionMap[$option->variant_group_id][$this->normalizeOption($option->value)] = $option;
        }

        // ---- Pass 1: read rows and group them by product name ----
        $errors = [];
        $productGroups = [];
        $rowNumber = 1; // Row 1 is header

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip blank or empty rows
            if (empty(array_filter($row, fn($val): bool => trim((string) $val) !== ''))) {
                continue;
            }

            $name = $this->cell($row, $nameIndex);
            if ($name === null) {
                $errors[] = "Row {$rowNumber}: Product name is required and cannot be blank.";

                continue;
            }

            $key = mb_strtolower($name);
            $productGroups[$key]['name'] ??= $name;
            $productGroups[$key]['rows'][] = [$rowNumber, $row];
        }

        fclose($handle);

        // ---- Pass 2: validate and create ----
        $importedCount = 0;
        $variantCount = 0;
        $batchSkus = [];

        DB::beginTransaction();

        try {
            foreach ($productGroups as $group) {
                $name = $group['name'];
                $rows = $group['rows'];

                // First non-empty value of a product-level column across this product's rows
                $pick = function (?int $i) use ($rows): ?string {
                    foreach ($rows as [, $r]) {
                        $v = $this->cell($r, $i);
                        if ($v !== null) {
                            return $v;
                        }
                    }

                    return null;
                };

                // Images (min 3, like the admin form)
                $imageList = [];
                $rawImages = $pick($imageIndex);
                if ($rawImages !== null) {
                    $imageList = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $rawImages))));
                }
                if ($imageList === []) {
                    $imageList = $defaultImagePaths;
                }
                if (count($imageList) < self::MIN_IMAGES) {
                    $errors[] = "Product '{$name}': at least " . self::MIN_IMAGES . ' images are required (found ' . count($imageList) . '). Add them in the "images" column or upload default images.';

                    continue;
                }

                // Validate every variant row
                $baseSku = Str::upper(Str::slug($name)) ?: 'SKU';
                $variantRows = [];
                $seenCombos = [];

                foreach ($rows as [$rowNum, $row]) {
                    $rawActualPrice = trim((string) ($row[$actualPriceIndex] ?? ''));
                    // Remove currency symbols or commas e.g. "₹1,200.00" -> "1200.00"
                    $cleanActualPrice = preg_replace('/[^\d.]/', '', $rawActualPrice);

                    if ($cleanActualPrice === '' || !is_numeric($cleanActualPrice)) {
                        $errors[] = "Row {$rowNum} ('{$name}'): Invalid actual price '{$rawActualPrice}'. Must be a positive number.";

                        continue;
                    }

                    $actualPrice = (float) $cleanActualPrice;

                    $discountedPrice = 0.0;
                    $rawDiscount = $this->cell($row, $discountedPriceIndex);
                    if ($rawDiscount !== null) {
                        $cleanDiscount = preg_replace('/[^\d.]/', '', $rawDiscount);

                        if ($cleanDiscount !== '' && is_numeric($cleanDiscount)) {
                            $discountedPrice = (float) $cleanDiscount;

                            if ($discountedPrice > $actualPrice) {
                                $errors[] = "Row {$rowNum} ('{$name}'): Discounted price ({$discountedPrice}) cannot be higher than actual price ({$actualPrice}).";

                                continue;
                            }
                        }
                    }

                    $stockQty = 10;
                    $rawStock = $this->cell($row, $stockIndex);
                    if ($rawStock !== null && is_numeric($rawStock)) {
                        $stockQty = max(0, (int) $rawStock);
                    }

                    // Resolve variant options (color, storage, ...) to VariantValue records
                    $options = [];
                    foreach ($variantColumns as $colIndex => $variantGroup) {
                        $cellValue = $this->cell($row, $colIndex);
                        if ($cellValue === null) {
                            continue;
                        }

                        $option = $optionMap[$variantGroup->id][$this->normalizeOption($cellValue)] ?? null;

                        if ($option === null && self::AUTO_CREATE_VALUES) {
                            $option = VariantValue::create([
                                'variant_group_id' => $variantGroup->id,
                                'value' => $cellValue,
                                'sku_suffix' => Str::upper(Str::slug($cellValue, '')),
                            ]);
                            $optionMap[$variantGroup->id][$this->normalizeOption($cellValue)] = $option;
                        }

                        if ($option === null) {
                            $available = collect($optionMap[$variantGroup->id] ?? [])->pluck('value')->implode(', ');
                            $errors[] = "Row {$rowNum} ('{$name}'): '{$cellValue}' is not an option of \"{$variantGroup->name}\". Available: {$available}.";

                            continue 2;
                        }

                        $options[] = $option;
                    }

                    if ($options === []) {
                        $errors[] = "Row {$rowNum} ('{$name}'): At least one variant option (e.g. color or storage) is required.";

                        continue;
                    }

                    $valueIds = collect($options)->pluck('id')->sort()->values()->all();
                    $comboKey = implode('-', $valueIds);

                    if (isset($seenCombos[$comboKey])) {
                        $errors[] = "Row {$rowNum} ('{$name}'): Duplicate variant combination, skipped.";

                        continue;
                    }

                    // SKU: use the CSV value, otherwise build one from product name + option suffixes
                    $providedSku = $this->cell($row, $skuIndex);
                    if ($providedSku !== null) {
                        $sku = Str::upper($providedSku);

                        if (isset($batchSkus[$sku]) || ProductVariant::where('sku', $sku)->exists()) {
                            $errors[] = "Row {$rowNum} ('{$name}'): SKU '{$sku}' already exists, skipped.";

                            continue;
                        }
                    } else {
                        $suffixes = collect($options)
                            ->map(fn(VariantValue $o): string => filled($o->sku_suffix) ? (string) $o->sku_suffix : Str::upper(Str::slug($o->value, '')))
                            ->implode('-');
                        $sku = $this->uniqueVariantSku(mb_substr($baseSku, 0, 100) . '-' . $suffixes, $batchSkus);
                    }

                    $seenCombos[$comboKey] = true;
                    $batchSkus[$sku] = true;

                    $variantRows[] = [
                        'sku' => $sku,
                        'actual_price' => $actualPrice,
                        'discounted_price' => $discountedPrice,
                        'stock_qty' => $stockQty,
                        'is_active' => $this->parseActive($this->cell($row, $isActiveIndex)),
                        'value_ids' => $valueIds,
                    ];
                }

                // Nothing valid for this product: errors were already recorded
                if ($variantRows === []) {
                    continue;
                }

                // Specifications: "Label=Value|Label=Value"
                $specs = [];
                $rawSpecs = $pick($specIndex);
                if ($rawSpecs !== null) {
                    foreach (explode('|', $rawSpecs) as $pair) {
                        [$label, $value] = array_pad(explode('=', $pair, 2), 2, null);
                        $label = trim((string) $label);

                        if ($label !== '') {
                            $specs[] = [$label, trim((string) $value) ?: null];
                        }
                    }
                }

                $product = Product::create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => $this->uniqueSlug($name),
                    'sku' => $baseSku,
                    'description' => $pick($descriptionIndex),
                    'is_active' => $this->parseActive($this->cell($rows[0][1], $isActiveIndex)),
                ]);

                foreach ($imageList as $position => $imgPath) {
                    $product->images()->create([
                        'path' => $imgPath,
                        'position' => $position,
                    ]);
                }

                foreach ($variantRows as $variantRow) {
                    $variant = $product->variants()->create([
                        'sku' => $variantRow['sku'],
                        'actual_price' => $variantRow['actual_price'],
                        'discounted_price' => $variantRow['discounted_price'],
                        'stock_qty' => $variantRow['stock_qty'],
                        'is_active' => $variantRow['is_active'],
                    ]);

                    $variant->values()->sync($variantRow['value_ids']);
                    $variantCount++;
                }

                foreach ($specs as $position => [$label, $value]) {
                    $product->specifications()->create([
                        'label' => $label,
                        'value' => $value,
                        'position' => $position,
                    ]);
                }

                $importedCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Import failed due to an unexpected error: ' . $e->getMessage());
        }

        if ($importedCount === 0 && !empty($errors)) {
            return back()->withInput()->with('error', 'No products could be imported. Please review the errors below:')->with('import_errors', $errors);
        }

        $successMessage = "Successfully imported {$importedCount} product(s) with {$variantCount} variant(s) into category \"{$category->name}\".";

        if (!empty($errors)) {
            return redirect()->route('admin.products.index')
                ->with('success', $successMessage)
                ->with('warning', count($errors) . ' row(s) had issues and were skipped.')
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.products.index')
            ->with('success', $successMessage);
    }

    /**
     * Trimmed cell value, or null when the column is missing or the cell is empty.
     *
     * @param  array<int, string|null>  $row
     */
    private function cell(array $row, ?int $index): ?string
    {
        if ($index === null) {
            return null;
        }

        $value = trim((string) ($row[$index] ?? ''));

        return $value === '' ? null : $value;
    }

    private function normalizeHeader(string $header): string
    {
        return str_replace([' ', '-'], '_', strtolower(trim($header)));
    }

    private function normalizeOption(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function parseActive(?string $value): bool
    {
        if ($value === null) {
            return true;
        }

        return !in_array(strtolower($value), ['0', 'false', 'inactive', 'no', 'off'], true);
    }

    /**
     * Resolve header column index by checking a list of possible aliases.
     *
     * @param  array<string, int>  $columnMap
     * @param  array<int, string>  $aliases
     */
    private function resolveColumnIndex(array $columnMap, array $aliases): ?int
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $columnMap)) {
                return $columnMap[$alias];
            }
        }

        return null;
    }

    /**
     * Generate a unique slug for the product.
     */
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

    /**
     * Generate a unique variant SKU.
     *
     * @param  array<string, bool>  $batchSkus
     */
    private function uniqueVariantSku(string $base, array $batchSkus): string
    {
        $base = Str::upper($base);
        $candidate = $base;
        $suffix = 1;

        while (isset($batchSkus[$candidate]) || ProductVariant::where('sku', $candidate)->exists()) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}