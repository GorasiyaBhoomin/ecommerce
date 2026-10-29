<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AdminLanguageController extends Controller
{
    public function index(): View
    {
        $jsonPath = database_path('data/languages.json');
        $presets = File::exists($jsonPath) ? json_decode(File::get($jsonPath), true) : [];

        return view('admin.languages.index', [
            'languages' => Language::orderByDesc('is_default')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'presets' => is_array($presets) ? $presets : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'native_name' => 'nullable|string|max:100',
            'code' => 'required|string|max:10|alpha_dash|unique:languages,code',
        ]);

        $data['code'] = strtolower($data['code']);
        Language::create($data + ['is_default' => false, 'is_active' => true]);

        File::ensureDirectoryExists(lang_path());
        // Provision translation file if not already existing
        $file = lang_path($data['code'].'.json');
        if (! File::exists($file)) {
            $en = lang_path('en.json');
            File::put($file, File::exists($en) ? File::get($en) : '{}');
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

    public function toggle(Language $language): RedirectResponse
    {
        if ($language->is_default) {
            return back()->with('error', 'The default language cannot be disabled.');
        }

        $language->update(['is_active' => ! $language->is_active]);

        $status = $language->is_active ? 'enabled' : 'disabled';

        return back()->with('success', "Language '{$language->name}' is now {$status}.");
    }

    public function makeDefault(Language $language): RedirectResponse
    {
        Language::where('is_default', true)->update(['is_default' => false]);
        $language->update(['is_default' => true, 'is_active' => true]);

        return back()->with('success', "'{$language->name}' is now the default store language.");
    }

    public function destroy(Language $language): RedirectResponse
    {
        if ($language->is_default) {
            return back()->with('error', 'The default language cannot be deleted.');
        }

        $name = $language->name;
        $language->delete();

        return back()->with('success', "Language '{$name}' has been deleted.");
    }
}
