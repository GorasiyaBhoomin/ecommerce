<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AdminLanguageController extends Controller
{
    public function index(Request $request): View
    {
        $jsonPath = database_path('data/languages.json');
        $presets = File::exists($jsonPath) ? json_decode(File::get($jsonPath), true) : [];

        $search = $request->string('search')->trim()->toString();
        if ($search === '') {
            $search = $request->string('q')->trim()->toString();
        }

        $statusFilter = $request->string('status')->trim()->toString();
        $defaultFilter = $request->string('default')->trim()->toString();

        $query = Language::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('native_name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
            });
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'disabled') {
            $query->where('is_active', false);
        }

        if ($defaultFilter === 'yes') {
            $query->where('is_default', true);
        } elseif ($defaultFilter === 'no') {
            $query->where('is_default', false);
        }

        // Sorting
        $allowedSorts = ['name', 'code', 'is_default', 'is_active', 'created_at'];
        $sortBy = $request->string('sort_by')->trim()->toString();
        $sortDir = strtolower($request->string('sort_dir', 'asc')->toString()) === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderByDesc('is_default')
                ->orderByDesc('is_active')
                ->orderBy('name');
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $languages = $query->paginate($perPage)->withQueryString();
        $totalCount = Language::count();
        $activeCount = Language::where('is_active', true)->count();

        return view('admin.languages.index', [
            'languages' => $languages,
            'presets' => is_array($presets) ? $presets : [],
            'search' => $search,
            'statusFilter' => $statusFilter,
            'defaultFilter' => $defaultFilter,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'perPage' => $perPage,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'native_name' => 'nullable|string|max:100',
            'code' => 'required|string|max:10|alpha_dash|unique:languages,code',
        ]);

        $data['code'] = strtolower($data['code']);
        $language = Language::create($data + ['is_default' => false, 'is_active' => true]);

        File::ensureDirectoryExists(lang_path());
        // Provision translation file if not already existing
        $file = lang_path($data['code'].'.json');
        if (! File::exists($file)) {
            $en = lang_path('en.json');
            File::put($file, File::exists($en) ? File::get($en) : '{}');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'language' => $language,
                'message' => "Language '{$data['name']}' has been added successfully.",
            ]);
        }

        return back()->with('success', "Language '{$data['name']}' has been added successfully.");
    }

    public function switchLanguage(Request $request, ?string $code = null): RedirectResponse
    {
        $code = $code ?? $request->input('code') ?? $request->input('locale');

        if ($code) {
            $code = strtolower(trim($code));
            $validCodes = Language::active()->pluck('code')->all();

            if (in_array($code, $validCodes, true)) {
                session(['locale' => $code]);
                app()->setLocale($code);
            }
        }

        return back();
    }

    public function toggle(Language $language)
    {
        if ($language->is_default) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The default language cannot be disabled.',
                ], 422);
            }

            return back()->with('error', 'The default language cannot be disabled.');
        }

        $language->update(['is_active' => ! $language->is_active]);

        $status = $language->is_active ? 'enabled' : 'disabled';
        $statusText = $language->is_active ? 'Active' : 'Inactive';

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $language->is_active,
                'status_text' => $statusText,
                'message' => "Language '{$language->name}' is now {$status}.",
            ]);
        }

        return back()->with('success', "Language '{$language->name}' is now {$status}.");
    }

    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'native_name' => 'nullable|string|max:100',
        ]);

        $language->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'language' => $language,
                'message' => "Language '{$language->name}' updated successfully.",
            ]);
        }

        return back()->with('success', "Language '{$language->name}' updated successfully.");
    }

    public function makeDefault(Language $language): RedirectResponse
    {
        Language::where('is_default', true)->update(['is_default' => false]);
        $language->update(['is_default' => true, 'is_active' => true]);

        return back()->with('success', "'{$language->name}' is now the default store language.");
    }

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The default language cannot be deleted.',
                ], 422);
            }

            return back()->with('error', 'The default language cannot be deleted.');
        }

        $name = $language->name;
        $language->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Language '{$name}' has been deleted.",
            ]);
        }

        return back()->with('success', "Language '{$name}' has been deleted.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:languages,id',
        ]);

        $ids = $request->input('ids', []);
        $languages = Language::whereIn('id', $ids)->get();

        $deletedCount = 0;
        $hasDefault = false;

        foreach ($languages as $lang) {
            if ($lang->is_default) {
                $hasDefault = true;

                continue;
            }
            $lang->delete();
            $deletedCount++;
        }

        $message = "Deleted {$deletedCount} language(s) successfully.";
        if ($hasDefault) {
            $message .= ' (Default language cannot be deleted and was skipped).';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
