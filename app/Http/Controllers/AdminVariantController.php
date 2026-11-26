<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\VariantGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminVariantController extends Controller
{
    /**
     * Build the query and shared data for the variants management views.
     */
    protected function getVariantGroupsData(Request $request): array
    {
        $search = $request->string('search')->trim()->toString();
        if ($search === '') {
            $search = $request->string('q')->trim()->toString();
        }

        $statusFilter = $request->string('status')->trim()->toString();
        $categoryIdFilter = $request->string('category_id')->trim()->toString();

        $query = VariantGroup::with(['values', 'categories']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('label', 'like', '%'.$search.'%')
                    ->orWhereHas('values', function ($vQ) use ($search) {
                        $vQ->where('value', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('categories', function ($cQ) use ($search) {
                        $cQ->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'hidden' || $statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($categoryIdFilter !== '' && $categoryIdFilter !== 'all') {
            $query->whereHas('categories', function ($cQ) use ($categoryIdFilter) {
                $cQ->where('categories.id', (int) $categoryIdFilter);
            });
        }

        // Sorting
        $allowedSorts = ['name', 'label', 'is_active', 'created_at'];
        $sortBy = $request->string('sort_by')->trim()->toString();
        $sortDir = strtolower($request->string('sort_dir', 'asc')->toString()) === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $variantGroups = $query->paginate($perPage)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $editGroup = null;
        if ($request->filled('edit')) {
            $editGroup = VariantGroup::with(['values', 'categories'])->find($request->input('edit'));
        }

        return [
            'variantGroups' => $variantGroups,
            'categories' => $categories,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'categoryIdFilter' => $categoryIdFilter,
            'perPage' => $perPage,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'editGroup' => $editGroup,
        ];
    }

    public function index(Request $request)
    {
        $data = $this->getVariantGroupsData($request);
        $data['editGroup'] = null;

        return view('admin.admin-variants', $data);
    }

    public function create(Request $request)
    {
        $data = $this->getVariantGroupsData($request);
        $data['editGroup'] = null;

        return view('admin.admin-add-variant', $data);
    }

    public function edit(Request $request, string $id)
    {
        $group = VariantGroup::with(['values', 'categories'])->findOrFail($id);
        $data = $this->getVariantGroupsData($request);
        $data['editGroup'] = $group;

        return view('admin.admin-edit-variant', $data);
    }

    public function showJson(string $id)
    {
        $group = VariantGroup::with(['values', 'categories'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'group' => $group,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:variant_groups,name',
            'label' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'category_id' => 'nullable|exists:categories,id',
            'values' => 'required|array|min:1',
            'values.*.value' => 'required|string|max:255',
            'values.*.sku_suffix' => 'nullable|string|max:255',
        ]);

        $group = DB::transaction(function () use ($data, $request) {
            $group = VariantGroup::create([
                'name' => $data['name'],
                'label' => $data['label'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $group->categories()->sync($data['category_id'] ? [$data['category_id']] : []);
            $group->values()->createMany($data['values']);

            return $group;
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Variant group created successfully.',
                'group' => $group->load(['values', 'categories']),
            ]);
        }

        return redirect()->route('admin.variants')->with('success', 'Variant group created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $group = VariantGroup::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:variant_groups,name,'.$group->id,
            'label' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'category_id' => 'nullable|exists:categories,id',
            'values' => 'required|array|min:1',
            'values.*.id' => 'nullable|integer',
            'values.*.value' => 'required|string|max:255',
            'values.*.sku_suffix' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $group, $request) {
            $group->update([
                'name' => $data['name'],
                'label' => $data['label'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $group->categories()->sync($data['category_id'] ? [$data['category_id']] : []);

            $keptValueIds = [];

            foreach ($data['values'] as $valueData) {
                $value = isset($valueData['id']) ? $group->values()->find($valueData['id']) : null;

                if ($value) {
                    $value->update($valueData);
                } else {
                    $value = $group->values()->create($valueData);
                }

                $keptValueIds[] = $value->id;
            }

            $group->values()->whereNotIn('id', $keptValueIds)->delete();
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Variant group updated successfully.',
                'group' => $group->fresh(['values', 'categories']),
            ]);
        }

        return redirect()->route('admin.variants')->with('success', 'Variant group updated successfully.');
    }

    public function destroy(Request $request, string $id)
    {
        $group = VariantGroup::findOrFail($id);
        $name = $group->name;

        DB::transaction(function () use ($group) {
            $group->categories()->detach();
            $group->values()->delete();
            $group->delete();
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Variant group \"{$name}\" deleted successfully.",
            ]);
        }

        return redirect()->route('admin.variants')->with('success', "Variant group \"{$name}\" deleted successfully.");
    }

    public function toggleStatus(VariantGroup $variant)
    {
        $variant->is_active = ! $variant->is_active;
        $variant->save();

        $statusText = $variant->is_active ? 'Active' : 'Inactive';

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $variant->is_active,
                'status_text' => $statusText,
                'message' => "Variant group \"{$variant->name}\" status updated to {$statusText}.",
            ]);
        }

        return back()->with('success', "Variant group \"{$variant->name}\" status updated to {$statusText}.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:variant_groups,id',
        ]);

        $ids = $request->input('ids', []);
        $deletedCount = 0;

        DB::transaction(function () use ($ids, &$deletedCount) {
            $groups = VariantGroup::whereIn('id', $ids)->get();
            foreach ($groups as $group) {
                $group->categories()->detach();
                $group->values()->delete();
                $group->delete();
                $deletedCount++;
            }
        });

        $message = "Deleted {$deletedCount} variant group(s) successfully.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => $message,
            ]);
        }

        return redirect()->route('admin.variants')->with('success', $message);
    }
}
