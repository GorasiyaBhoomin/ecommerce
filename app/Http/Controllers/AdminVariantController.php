<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\VariantGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminVariantController extends Controller
{
    public function index()
    {
        return view('admin.admin-variants', [
            'variantGroups' => VariantGroup::with(['values', 'categories'])->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.admin-add-variant', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:variant_groups,name',
            'label' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'values' => 'required|array|min:1',
            'values.*.value' => 'required|string|max:255',
            'values.*.sku_suffix' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $request) {
            $group = VariantGroup::create([
                'name' => $data['name'],
                'label' => $data['label'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $group->categories()->sync($data['category_id'] ? [$data['category_id']] : []);
            $group->values()->createMany($data['values']);
        });

        return redirect()->route('admin.variants')->with('success', 'Variant group created successfully.');
    }

    public function edit(string $id)
    {
        return view('admin.admin-edit-variant', [
            'group' => VariantGroup::with(['values', 'categories'])->findOrFail($id),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $group = VariantGroup::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:variant_groups,name,'.$group->id,
            'label' => 'nullable|string|max:255',
            'is_active' => 'boolean',
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

        return redirect()->route('admin.variants')->with('success', 'Variant group updated successfully.');
    }

    public function destroy(string $id)
    {
        VariantGroup::findOrFail($id)->delete();

        return redirect()->route('admin.variants')->with('success', 'Variant group deleted successfully.');
    }
}
