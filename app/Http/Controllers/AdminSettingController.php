<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    /**
     * Show the system settings page.
     */
    public function index(): View
    {
        $settings = Setting::allAsAssoc();

        $colorPresets = [
            'emerald' => [
                'name' => 'Emerald Teal (Default)',
                'primary' => '#0F766E',
                'primary_hover' => '#0D655E',
                'sidebar_bg' => '#0F2A27',
                'sidebar_text' => '#CFE3DF',
                'sidebar_active' => '#1D4E49',
                'sidebar_hover' => '#173E3A',
                'accent' => '#F59E0B',
                'body_bg' => '#F4F6F5',
                'card_bg' => '#FFFFFF',
            ],
            'navy' => [
                'name' => 'Royal Indigo',
                'primary' => '#4338CA',
                'primary_hover' => '#3730A3',
                'sidebar_bg' => '#1E1B4B',
                'sidebar_text' => '#E0E7FF',
                'sidebar_active' => '#312E81',
                'sidebar_hover' => '#2E2868',
                'accent' => '#F43F5E',
                'body_bg' => '#F8FAFC',
                'card_bg' => '#FFFFFF',
            ],
            'slate' => [
                'name' => 'Midnight Slate',
                'primary' => '#2563EB',
                'primary_hover' => '#1D4ED8',
                'sidebar_bg' => '#0F172A',
                'sidebar_text' => '#CBD5E1',
                'sidebar_active' => '#1E293B',
                'sidebar_hover' => '#1E293B',
                'accent' => '#38BDF8',
                'body_bg' => '#F1F5F9',
                'card_bg' => '#FFFFFF',
            ],
            'crimson' => [
                'name' => 'Crimson Luxury',
                'primary' => '#BE123C',
                'primary_hover' => '#9F1239',
                'sidebar_bg' => '#4C0519',
                'sidebar_text' => '#FECDD3',
                'sidebar_active' => '#881337',
                'sidebar_hover' => '#670C25',
                'accent' => '#F59E0B',
                'body_bg' => '#FFF1F2',
                'card_bg' => '#FFFFFF',
            ],
            'dark' => [
                'name' => 'Obsidian Black',
                'primary' => '#10B981',
                'primary_hover' => '#059669',
                'sidebar_bg' => '#111827',
                'sidebar_text' => '#9CA3AF',
                'sidebar_active' => '#1F2937',
                'sidebar_hover' => '#1F2937',
                'accent' => '#FBBF24',
                'body_bg' => '#F9FAFB',
                'card_bg' => '#FFFFFF',
            ],
        ];

        return view('admin.admin-settings', compact('settings', 'colorPresets'));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Store Identity
            'store_name' => 'required|string|max:100',
            'store_tagline' => 'nullable|string|max:255',
            'store_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:4096',
            'remove_store_logo' => 'nullable|boolean',
            'store_favicon' => 'nullable|file|mimes:ico,png,svg|max:2048',
            'remove_store_favicon' => 'nullable|boolean',

            // Contact & Localization
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:40',
            'contact_address' => 'nullable|string|max:500',
            'currency_symbol' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:10',
            'free_delivery_threshold' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',

            // Admin Theme Colors
            'admin_primary_color' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'admin_primary_hover' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'admin_sidebar_bg' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'admin_sidebar_text' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'admin_sidebar_active' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'admin_sidebar_hover' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'admin_accent_color' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'admin_body_bg' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'admin_card_bg' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        $uploadDir = public_path('uploads/settings');
        File::ensureDirectoryExists($uploadDir);

        // Handle Store Logo
        if ($request->boolean('remove_store_logo')) {
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo && File::exists(public_path($oldLogo))) {
                File::delete(public_path($oldLogo));
            }
            Setting::set('store_logo', null, 'identity');
        } elseif ($request->hasFile('store_logo')) {
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo && File::exists(public_path($oldLogo))) {
                File::delete(public_path($oldLogo));
            }

            $logoFile = $request->file('store_logo');
            $logoName = 'logo_'.time().'.'.$logoFile->getClientOriginalExtension();
            $logoFile->move($uploadDir, $logoName);
            Setting::set('store_logo', 'uploads/settings/'.$logoName, 'identity');
        }

        // Handle Store Favicon
        if ($request->boolean('remove_store_favicon')) {
            $oldFavicon = Setting::get('store_favicon');
            if ($oldFavicon && File::exists(public_path($oldFavicon))) {
                File::delete(public_path($oldFavicon));
            }
            Setting::set('store_favicon', null, 'identity');
        } elseif ($request->hasFile('store_favicon')) {
            $oldFavicon = Setting::get('store_favicon');
            if ($oldFavicon && File::exists(public_path($oldFavicon))) {
                File::delete(public_path($oldFavicon));
            }

            $faviconFile = $request->file('store_favicon');
            $faviconName = 'favicon_'.time().'.'.$faviconFile->getClientOriginalExtension();
            $faviconFile->move($uploadDir, $faviconName);
            Setting::set('store_favicon', 'uploads/settings/'.$faviconName, 'identity');
        }

        // Store Identity
        if (isset($validated['store_name'])) {
            Setting::set('store_name', $validated['store_name'], 'identity');
        }
        if (array_key_exists('store_tagline', $validated)) {
            Setting::set('store_tagline', $validated['store_tagline'] ?? '', 'identity');
        }

        // Contact & Localization
        if (array_key_exists('contact_email', $validated)) {
            Setting::set('contact_email', $validated['contact_email'] ?? '', 'contact');
        }
        if (array_key_exists('contact_phone', $validated)) {
            Setting::set('contact_phone', $validated['contact_phone'] ?? '', 'contact');
        }
        if (array_key_exists('contact_address', $validated)) {
            Setting::set('contact_address', $validated['contact_address'] ?? '', 'contact');
        }
        if (array_key_exists('currency_symbol', $validated)) {
            Setting::set('currency_symbol', $validated['currency_symbol'] ?: '₹', 'localization');
        }
        if (array_key_exists('currency_code', $validated)) {
            Setting::set('currency_code', strtoupper($validated['currency_code'] ?: 'INR'), 'localization');
        }
        if (array_key_exists('free_delivery_threshold', $validated)) {
            Setting::set('free_delivery_threshold', (string) ($validated['free_delivery_threshold'] ?? 499), 'shipping');
        }
        if (array_key_exists('delivery_fee', $validated)) {
            Setting::set('delivery_fee', (string) ($validated['delivery_fee'] ?? 49), 'shipping');
        }

        // Admin Colors
        $colorFields = [
            'admin_primary_color',
            'admin_primary_hover',
            'admin_sidebar_bg',
            'admin_sidebar_text',
            'admin_sidebar_active',
            'admin_sidebar_hover',
            'admin_accent_color',
            'admin_body_bg',
            'admin_card_bg',
        ];

        foreach ($colorFields as $field) {
            if (! empty($validated[$field])) {
                Setting::set($field, $validated[$field], 'appearance');
            }
        }

        Setting::forgetCache();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'System settings and admin colors updated successfully!',
            ]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'System settings and admin colors updated successfully!');
    }

    /**
     * Reset admin panel colors back to defaults.
     */
    public function resetColors(): RedirectResponse
    {
        $defaults = Setting::defaults();

        $colorFields = [
            'admin_primary_color',
            'admin_primary_hover',
            'admin_sidebar_bg',
            'admin_sidebar_text',
            'admin_sidebar_active',
            'admin_sidebar_hover',
            'admin_accent_color',
            'admin_body_bg',
            'admin_card_bg',
        ];

        foreach ($colorFields as $field) {
            Setting::set($field, $defaults[$field] ?? '#0F766E', 'appearance');
        }

        Setting::forgetCache();

        return redirect()->route('admin.settings.index')->with('success', 'Admin panel theme colors reset to default Emerald Teal theme!');
    }
}
