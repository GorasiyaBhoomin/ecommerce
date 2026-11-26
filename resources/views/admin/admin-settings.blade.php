<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>System Settings &amp; Theme Colors | {{ setting('store_name', 'Shopkart') }} Admin</title>

  @if (setting('store_favicon'))
    <link rel="icon" href="{{ asset(setting('store_favicon')) }}">
  @endif

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">

  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">

  <style>
    .settings-grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 24px;
    }

    .settings-col-8 {
      grid-column: span 8;
    }

    .settings-col-4 {
      grid-column: span 4;
    }

    @media (max-width: 1024px) {

      .settings-col-8,
      .settings-col-4 {
        grid-column: span 12;
      }
    }

    .settings-card {
      background: var(--card-bg, #FFFFFF);
      border: 1px solid var(--border, #E2E8F0);
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      transition: box-shadow 0.2s ease;
    }

    .settings-card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding-bottom: 18px;
      margin-bottom: 20px;
      border-bottom: 1px solid var(--border, #E2E8F0);
    }

    .settings-icon-badge {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: var(--light-teal, #E6F4F1);
      color: var(--primary-teal, #0F766E);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .settings-card-title {
      font-size: 17px;
      font-weight: 700;
      color: var(--ink, #0F172A);
      margin: 0;
    }

    .settings-card-subtitle {
      font-size: 13px;
      color: var(--muted, #566579);
      margin: 2px 0 0 0;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--ink, #0F172A);
      margin-bottom: 6px;
    }

    .form-help {
      font-size: 12px;
      color: var(--muted, #566579);
      margin-top: 4px;
    }

    .form-control {
      width: 100%;
      padding: 10px 14px;
      font-size: 14px;
      font-family: inherit;
      color: var(--ink, #0F172A);
      background: #FFFFFF;
      border: 1px solid var(--border, #E2E8F0);
      border-radius: 10px;
      outline: none;
      transition: border-color 0.15s ease, box-shadow 0.15s ease;
      box-sizing: border-box;
    }

    .form-control:focus {
      border-color: var(--primary-teal, #0F766E);
      box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
    }

    .form-row {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
    }

    .form-col {
      flex: 1 1 200px;
    }

    .upload-box {
      border: 2px dashed var(--border, #E2E8F0);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      background: #FAFBFB;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
    }

    .upload-box:hover {
      border-color: var(--primary-teal, #0F766E);
      background: var(--light-teal, #E6F4F1);
    }

    .preset-pill {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border: 1px solid var(--border, #E2E8F0);
      border-radius: 12px;
      background: #FFFFFF;
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: left;
      font-size: 13px;
      font-weight: 600;
      color: var(--ink, #0F172A);
    }

    .preset-pill:hover {
      border-color: var(--primary-teal, #0F766E);
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .preset-swatch {
      width: 22px;
      height: 22px;
      border-radius: 6px;
      flex-shrink: 0;
      border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .color-input-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #FFFFFF;
      border: 1px solid var(--border, #E2E8F0);
      border-radius: 10px;
      padding: 6px 10px;
    }

    .color-picker-native {
      width: 34px;
      height: 34px;
      border: none;
      padding: 0;
      background: transparent;
      cursor: pointer;
      border-radius: 6px;
    }

    .color-picker-native::-webkit-color-swatch-wrapper {
      padding: 0;
    }

    .color-picker-native::-webkit-color-swatch {
      border: 1px solid rgba(0, 0, 0, 0.15);
      border-radius: 6px;
    }

    .color-hex-text {
      border: none;
      outline: none;
      font-family: monospace;
      font-size: 13px;
      font-weight: 600;
      color: var(--ink, #0F172A);
      width: 85px;
      text-transform: uppercase;
    }

    .theme-preview-card {
      border-radius: 14px;
      overflow: hidden;
      border: 1px solid var(--border, #E2E8F0);
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }
  </style>
</head>

<body>

  <div class="admin-container">
    @include('admin.partials.admin-sidebar', ['active' => 'settings', 'showUserPill' => true])

    <!-- Main Content Area -->
    <main class="admin-main">
      <header class="top-header">
        <div class="header-title-wrap">
          <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="4" x2="20" y1="12" y2="12" />
              <line x1="4" x2="20" y1="6" y2="6" />
              <line x1="4" x2="20" y1="18" y2="18" />
            </svg>
          </button>
          <div>
            <h1 class="page-heading">{{ __('System Settings') }}</h1>
            <div style="font-size: 13px; color: var(--muted, #566579);">
              {{ __('Manage store branding, contact info, shipping rules, and customize Admin Panel theme colors.') }}
            </div>
          </div>
        </div>

        <div class="header-actions">
          <form method="POST" action="{{ route('admin.settings.reset-colors') }}" style="margin: 0;"
            onsubmit="return confirm('Reset all Admin Panel colors back to default Emerald Teal?');">
            @csrf
            <button type="submit" class="btn-secondary" style="color: #64748B;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
                <path d="M21 3v5h-5" />
                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
                <path d="M8 16H3v5" />
              </svg>
              {{ __('Reset Theme Colors') }}
            </button>
          </form>

          <a href="/index" target="_blank" class="btn-secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
              <polyline points="15 3 21 3 21 9" />
              <line x1="10" y1="14" x2="21" y2="3" />
            </svg>
            {{ __('View Store') }}
          </a>

          @include('admin.partials.notification-bar')
        </div>
      </header>

      <div class="content-inner">
        @if (session('success'))
          <div
            style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M20 6 9 17l-5-5" />
            </svg>
            <span>{{ session('success') }}</span>
          </div>
        @endif

        @if ($errors->any())
          <div
            style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; font-size: 13px; font-weight: 600;">
            <div style="font-size: 14px; margin-bottom: 6px;">{{ __('Please resolve the following errors:') }}</div>
            <ul style="margin: 0; padding-left: 20px;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data"
          id="settingsForm" class="ajax-form" data-reset="false">
          @csrf

          <div class="settings-grid">
            <!-- Left Column: Store Details & Localization -->
            <div class="settings-col-8">

              <!-- 1. Store Identity & Logo -->
              <div class="settings-card">
                <div class="settings-card-header">
                  <div class="settings-icon-badge">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                      <path d="M3 6h18" />
                      <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                  </div>
                  <div>
                    <h2 class="settings-card-title">{{ __('Store Identity & Branding') }}</h2>
                    <p class="settings-card-subtitle">{{ __('Store name, logo image, favicon, and brand subtitle.') }}
                    </p>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label" for="store_name">{{ __('Store Name') }} *</label>
                      <input type="text" id="store_name" name="store_name" class="form-control"
                        value="{{ old('store_name', $settings['store_name'] ?? 'Shopkart') }}" required
                        oninput="updatePreviewBrandName(this.value)">
                      <div class="form-help">
                        {{ __('Displayed in header, invoices, customer emails, and page titles.') }}
                      </div>
                    </div>
                  </div>
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label" for="store_tagline">{{ __('Store Tagline / Slogan') }}</label>
                      <input type="text" id="store_tagline" name="store_tagline" class="form-control"
                        value="{{ old('store_tagline', $settings['store_tagline'] ?? '') }}"
                        placeholder="e.g. Everyday Shopping Destination">
                    </div>
                  </div>
                </div>

                <div class="form-row" style="margin-top: 10px;">
                  <!-- Logo Upload -->
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label">{{ __('Store Logo') }}</label>
                      <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 10px;">
                        <div id="logoPreviewWrap"
                          style="width: 72px; height: 72px; border-radius: 12px; border: 1px solid var(--border); background: #FAFBFB; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 6px; box-sizing: border-box;">
                          @if (!empty($settings['store_logo']) && file_exists(public_path($settings['store_logo'])))
                            <img id="logoPreviewImg" src="{{ asset($settings['store_logo']) }}" alt="Logo"
                              style="max-width: 100%; max-height: 100%; object-fit: contain;">
                          @else
                            <svg id="logoPlaceholderSvg" width="30" height="30" viewBox="0 0 24 24" fill="none"
                              stroke="#94A3B8" stroke-width="2">
                              <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                              <circle cx="9" cy="9" r="2" />
                              <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                            </svg>
                            <img id="logoPreviewImg" src="" alt="Logo"
                              style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;">
                          @endif
                        </div>
                        <div style="flex: 1;">
                          <input type="file" id="store_logo" name="store_logo"
                            accept="image/png,image/jpeg,image/webp,image/svg+xml" style="display: none;"
                            onchange="previewSelectedImage(this, 'logoPreviewImg', 'logoPlaceholderSvg')">
                          <button type="button" class="btn-secondary" style="font-size: 13px; padding: 7px 14px;"
                            onclick="document.getElementById('store_logo').click()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                              <polyline points="17 8 12 3 7 8" />
                              <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                            {{ __('Choose Logo') }}
                          </button>
                          <div class="form-help">{{ __('PNG, JPG, WebP, or SVG. Max 4MB. Recommended: 240x60px.') }}
                          </div>
                        </div>
                      </div>
                      @if (!empty($settings['store_logo']))
                        <label
                          style="font-size: 12px; color: #DC2626; display: flex; align-items: center; gap: 6px; cursor: pointer; margin-top: 6px;">
                          <input type="checkbox" name="remove_store_logo" value="1">
                          {{ __('Remove custom logo (revert to default icon)') }}
                        </label>
                      @endif
                    </div>
                  </div>

                  <!-- Favicon Upload -->
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label">{{ __('Store Favicon') }}</label>
                      <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 10px;">
                        <div id="faviconPreviewWrap"
                          style="width: 48px; height: 48px; border-radius: 10px; border: 1px solid var(--border); background: #FAFBFB; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px; box-sizing: border-box;">
                          @if (!empty($settings['store_favicon']) && file_exists(public_path($settings['store_favicon'])))
                            <img id="faviconPreviewImg" src="{{ asset($settings['store_favicon']) }}" alt="Favicon"
                              style="max-width: 100%; max-height: 100%; object-fit: contain;">
                          @else
                            <svg id="faviconPlaceholderSvg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                              stroke="#94A3B8" stroke-width="2">
                              <circle cx="12" cy="12" r="10" />
                              <line x1="2" y1="12" x2="22" y2="12" />
                              <path
                                d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                            </svg>
                            <img id="faviconPreviewImg" src="" alt="Favicon"
                              style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;">
                          @endif
                        </div>
                        <div style="flex: 1;">
                          <input type="file" id="store_favicon" name="store_favicon"
                            accept="image/x-icon,image/png,image/svg+xml" style="display: none;"
                            onchange="previewSelectedImage(this, 'faviconPreviewImg', 'faviconPlaceholderSvg')">
                          <button type="button" class="btn-secondary" style="font-size: 13px; padding: 7px 14px;"
                            onclick="document.getElementById('store_favicon').click()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                              <polyline points="17 8 12 3 7 8" />
                              <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                            {{ __('Choose Favicon') }}
                          </button>
                          <div class="form-help">{{ __('ICO, PNG, or SVG (32x32px recommended).') }}</div>
                        </div>
                      </div>
                      @if (!empty($settings['store_favicon']))
                        <label
                          style="font-size: 12px; color: #DC2626; display: flex; align-items: center; gap: 6px; cursor: pointer; margin-top: 6px;">
                          <input type="checkbox" name="remove_store_favicon" value="1">
                          {{ __('Remove custom favicon') }}
                        </label>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <!-- 2. Contact Details & Shipping -->
              <div class="settings-card">
                <div class="settings-card-header">
                  <div class="settings-icon-badge">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path
                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                  </div>
                  <div>
                    <h2 class="settings-card-title">{{ __('Contact & Shipping Configuration') }}</h2>
                    <p class="settings-card-subtitle">
                      {{ __('Customer support contact, invoice address, and delivery charges.') }}
                    </p>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label" for="contact_email">{{ __('Support Email') }}</label>
                      <input type="email" id="contact_email" name="contact_email" class="form-control"
                        value="{{ old('contact_email', $settings['contact_email'] ?? '') }}"
                        placeholder="support@example.com">
                    </div>
                  </div>
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label" for="contact_phone">{{ __('Support Phone') }}</label>
                      <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                        value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}"
                        placeholder="+91 98765 43210">
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label" for="contact_address">{{ __('Store Physical Address') }}</label>
                  <textarea id="contact_address" name="contact_address" rows="2" class="form-control"
                    placeholder="Store address printed on PDF invoices">{{ old('contact_address', $settings['contact_address'] ?? '') }}</textarea>
                </div>

                <div class="form-row">
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label" for="currency_symbol">{{ __('Currency Symbol') }}</label>
                      <input type="text" id="currency_symbol" name="currency_symbol" class="form-control"
                        value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '₹') }}">
                    </div>
                  </div>
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label" for="currency_code">{{ __('Currency Code') }}</label>
                      <input type="text" id="currency_code" name="currency_code" class="form-control"
                        value="{{ old('currency_code', $settings['currency_code'] ?? 'INR') }}">
                    </div>
                  </div>
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label" for="delivery_fee">{{ __('Standard Delivery Fee') }}</label>
                      <input type="number" step="0.01" id="delivery_fee" name="delivery_fee" class="form-control"
                        value="{{ old('delivery_fee', $settings['delivery_fee'] ?? 49) }}">
                    </div>
                  </div>
                  <div class="form-col">
                    <div class="form-group">
                      <label class="form-label" for="free_delivery_threshold">{{ __('Free Delivery Over') }}</label>
                      <input type="number" step="0.01" id="free_delivery_threshold" name="free_delivery_threshold"
                        class="form-control"
                        value="{{ old('free_delivery_threshold', $settings['free_delivery_threshold'] ?? 499) }}">
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- Right Column: Admin Panel Theme Colors (Live Customizer) -->
            <div class="settings-col-4">

              <div class="settings-card" style="position: sticky; top: 20px;">
                <div class="settings-card-header">
                  <div class="settings-icon-badge" style="background: #FEF3C7; color: #D97706;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="13.5" cy="6.5" r=".5" fill="currentColor" />
                      <circle cx="17.5" cy="10.5" r=".5" fill="currentColor" />
                      <circle cx="8.5" cy="7.5" r=".5" fill="currentColor" />
                      <circle cx="6.5" cy="12.5" r=".5" fill="currentColor" />
                      <path
                        d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2Z" />
                    </svg>
                  </div>
                  <div>
                    <h2 class="settings-card-title">{{ __('Admin Panel Colors') }}</h2>
                    <p class="settings-card-subtitle">{{ __('Customize colors across the entire admin suite.') }}</p>
                  </div>
                </div>

                <!-- Theme Presets -->
                <div class="form-group">
                  <label class="form-label">{{ __('Quick Theme Presets') }}</label>
                  <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach ($colorPresets as $key => $preset)
                      <div class="preset-pill" onclick="applyColorPreset({{ json_encode($preset) }})">
                        <span class="preset-swatch" style="background: {{ $preset['primary'] }};"></span>
                        <span class="preset-swatch" style="background: {{ $preset['sidebar_bg'] }};"></span>
                        <span style="flex: 1;">{{ $preset['name'] }}</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2.5">
                          <polyline points="9 18 15 12 9 6" />
                        </svg>
                      </div>
                    @endforeach
                  </div>
                </div>

                <!-- Color Fields -->
                <div style="margin-top: 18px; border-top: 1px solid var(--border); padding-top: 18px;">
                  <div class="form-group">
                    <label class="form-label" for="color_primary">{{ __('Primary Button & Highlight') }}</label>
                    <div class="color-input-wrap">
                      <input type="color" id="color_primary_picker" class="color-picker-native"
                        value="{{ old('admin_primary_color', $settings['admin_primary_color'] ?? '#0F766E') }}"
                        oninput="syncColorValue('color_primary_picker', 'admin_primary_color', '--preview-primary')">
                      <input type="text" id="admin_primary_color" name="admin_primary_color" class="color-hex-text"
                        value="{{ old('admin_primary_color', $settings['admin_primary_color'] ?? '#0F766E') }}"
                        maxlength="7"
                        oninput="syncHexToPicker('admin_primary_color', 'color_primary_picker', '--preview-primary')">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="color_primary_hover">{{ __('Primary Hover State') }}</label>
                    <div class="color-input-wrap">
                      <input type="color" id="color_hover_picker" class="color-picker-native"
                        value="{{ old('admin_primary_hover', $settings['admin_primary_hover'] ?? '#0D655E') }}"
                        oninput="syncColorValue('color_hover_picker', 'admin_primary_hover')">
                      <input type="text" id="admin_primary_hover" name="admin_primary_hover" class="color-hex-text"
                        value="{{ old('admin_primary_hover', $settings['admin_primary_hover'] ?? '#0D655E') }}"
                        maxlength="7" oninput="syncHexToPicker('admin_primary_hover', 'color_hover_picker')">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="admin_sidebar_bg">{{ __('Sidebar Background') }}</label>
                    <div class="color-input-wrap">
                      <input type="color" id="color_sidebar_picker" class="color-picker-native"
                        value="{{ old('admin_sidebar_bg', $settings['admin_sidebar_bg'] ?? '#0F2A27') }}"
                        oninput="syncColorValue('color_sidebar_picker', 'admin_sidebar_bg', '--preview-sidebar')">
                      <input type="text" id="admin_sidebar_bg" name="admin_sidebar_bg" class="color-hex-text"
                        value="{{ old('admin_sidebar_bg', $settings['admin_sidebar_bg'] ?? '#0F2A27') }}" maxlength="7"
                        oninput="syncHexToPicker('admin_sidebar_bg', 'color_sidebar_picker', '--preview-sidebar')">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="admin_sidebar_text">{{ __('Sidebar Text & Icons') }}</label>
                    <div class="color-input-wrap">
                      <input type="color" id="color_sidebar_text_picker" class="color-picker-native"
                        value="{{ old('admin_sidebar_text', $settings['admin_sidebar_text'] ?? '#CFE3DF') }}"
                        oninput="syncColorValue('color_sidebar_text_picker', 'admin_sidebar_text', '--preview-sidebar-text')">
                      <input type="text" id="admin_sidebar_text" name="admin_sidebar_text" class="color-hex-text"
                        value="{{ old('admin_sidebar_text', $settings['admin_sidebar_text'] ?? '#CFE3DF') }}"
                        maxlength="7"
                        oninput="syncHexToPicker('admin_sidebar_text', 'color_sidebar_text_picker', '--preview-sidebar-text')">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label"
                      for="admin_sidebar_active">{{ __('Sidebar Active Tab Background') }}</label>
                    <div class="color-input-wrap">
                      <input type="color" id="color_sidebar_active_picker" class="color-picker-native"
                        value="{{ old('admin_sidebar_active', $settings['admin_sidebar_active'] ?? '#1D4E49') }}"
                        oninput="syncColorValue('color_sidebar_active_picker', 'admin_sidebar_active', '--preview-sidebar-active')">
                      <input type="text" id="admin_sidebar_active" name="admin_sidebar_active" class="color-hex-text"
                        value="{{ old('admin_sidebar_active', $settings['admin_sidebar_active'] ?? '#1D4E49') }}"
                        maxlength="7"
                        oninput="syncHexToPicker('admin_sidebar_active', 'color_sidebar_active_picker', '--preview-sidebar-active')">
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="admin_accent_color">{{ __('Accent Highlight') }}</label>
                    <div class="color-input-wrap">
                      <input type="color" id="color_accent_picker" class="color-picker-native"
                        value="{{ old('admin_accent_color', $settings['admin_accent_color'] ?? '#F59E0B') }}"
                        oninput="syncColorValue('color_accent_picker', 'admin_accent_color', '--preview-accent')">
                      <input type="text" id="admin_accent_color" name="admin_accent_color" class="color-hex-text"
                        value="{{ old('admin_accent_color', $settings['admin_accent_color'] ?? '#F59E0B') }}"
                        maxlength="7"
                        oninput="syncHexToPicker('admin_accent_color', 'color_accent_picker', '--preview-accent')">
                    </div>
                  </div>
                </div>

                <!-- Live Mini Preview -->
                <div style="margin-top: 18px; border-top: 1px solid var(--border); padding-top: 16px;">
                  <label class="form-label">{{ __('Live Theme Preview') }}</label>
                  <div id="livePreviewBox" class="theme-preview-card" style="display: flex; height: 130px; background: #FFFFFF;
                              --preview-primary: {{ $settings['admin_primary_color'] ?? '#0F766E' }};
                              --preview-sidebar: {{ $settings['admin_sidebar_bg'] ?? '#0F2A27' }};
                              --preview-sidebar-text: {{ $settings['admin_sidebar_text'] ?? '#CFE3DF' }};
                              --preview-sidebar-active: {{ $settings['admin_sidebar_active'] ?? '#1D4E49' }};
                              --preview-accent: {{ $settings['admin_accent_color'] ?? '#F59E0B' }};">
                    <!-- Mini Sidebar -->
                    <div
                      style="width: 90px; background: var(--preview-sidebar); padding: 12px 8px; display: flex; flex-direction: column; gap: 8px;">
                      <div id="previewBrandName"
                        style="color: #FFFFFF; font-size: 11px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $settings['store_name'] ?? 'Shopkart' }}
                      </div>
                      <div
                        style="background: var(--preview-sidebar-active); color: #FFFFFF; padding: 4px 6px; border-radius: 6px; font-size: 9px; font-weight: 700;">
                        Orders
                      </div>
                      <div style="color: var(--preview-sidebar-text); padding: 4px 6px; font-size: 9px; opacity: 0.85;">
                        Products
                      </div>
                      <div style="color: var(--preview-sidebar-text); padding: 4px 6px; font-size: 9px; opacity: 0.85;">
                        Settings
                      </div>
                    </div>
                    <!-- Mini Content -->
                    <div
                      style="flex: 1; padding: 12px; background: #F8FAFC; display: flex; flex-direction: column; justify-content: space-between;">
                      <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 700; color: #0F172A;">Dashboard</span>
                        <span
                          style="width: 8px; height: 8px; border-radius: 99px; background: var(--preview-accent);"></span>
                      </div>
                      <div
                        style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 8px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 10px; color: #64748B;">Total Orders</span>
                        <button type="button"
                          style="background: var(--preview-primary); color: #FFFFFF; border: none; border-radius: 4px; padding: 3px 8px; font-size: 9px; font-weight: 700;">
                          Add
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Save Action Button -->
                <div style="margin-top: 24px;">
                  <button type="submit" class="btn-primary"
                    style="width: 100%; justify-content: center; padding: 12px; font-size: 14px; font-weight: 700; border-radius: 10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2.2">
                      <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                      <polyline points="17 21 17 13 7 13 7 21" />
                      <polyline points="7 3 7 8 15 8" />
                    </svg>
                    {{ __('Save All Settings') }}
                  </button>
                </div>

              </div>

            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script src="{{ asset('assets/js/ajax.js') }}"></script>
  <script>
    // Live preview helpers
    function syncColorValue(pickerId, textId, cssVarName) {
      const picker = document.getElementById(pickerId);
      const text = document.getElementById(textId);
      text.value = picker.value.toUpperCase();
      if (cssVarName) {
        document.getElementById('livePreviewBox').style.setProperty(cssVarName, picker.value);
      }
    }

    function syncHexToPicker(textId, pickerId, cssVarName) {
      const text = document.getElementById(textId);
      const picker = document.getElementById(pickerId);
      const val = text.value.trim();
      if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
        picker.value = val;
        if (cssVarName) {
          document.getElementById('livePreviewBox').style.setProperty(cssVarName, val);
        }
      }
    }

    function applyColorPreset(preset) {
      // Set values
      const mappings = [
        { text: 'admin_primary_color', picker: 'color_primary_picker', val: preset.primary, varName: '--preview-primary' },
        { text: 'admin_primary_hover', picker: 'color_hover_picker', val: preset.primary_hover },
        { text: 'admin_sidebar_bg', picker: 'color_sidebar_picker', val: preset.sidebar_bg, varName: '--preview-sidebar' },
        { text: 'admin_sidebar_text', picker: 'color_sidebar_text_picker', val: preset.sidebar_text, varName: '--preview-sidebar-text' },
        { text: 'admin_sidebar_active', picker: 'color_sidebar_active_picker', val: preset.sidebar_active, varName: '--preview-sidebar-active' },
        { text: 'admin_accent_color', picker: 'color_accent_picker', val: preset.accent, varName: '--preview-accent' }
      ];

      mappings.forEach(m => {
        const textEl = document.getElementById(m.text);
        const pickerEl = document.getElementById(m.picker);
        if (textEl && pickerEl) {
          textEl.value = m.val.toUpperCase();
          pickerEl.value = m.val;
          if (m.varName) {
            document.getElementById('livePreviewBox').style.setProperty(m.varName, m.val);
          }
        }
      });
    }

    function updatePreviewBrandName(name) {
      const el = document.getElementById('previewBrandName');
      if (el) {
        el.textContent = name || 'Shopkart';
      }
    }

    function previewSelectedImage(input, imgId, placeholderId) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          const img = document.getElementById(imgId);
          const placeholder = document.getElementById(placeholderId);
          if (img) {
            img.src = e.target.result;
            img.style.display = 'block';
          }
          if (placeholder) {
            placeholder.style.display = 'none';
          }
        };
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="{{ asset('assets/js/ajax.js') }}"></script>
</body>

</html>