<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ __('Languages') }} | Shopkart Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminproducts.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
  <style>
    /* Dedicated language panel styles */
    .lang-stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }

    .lang-stat-card {
      background: var(--card-bg, #FFFFFF);
      border: 1px solid var(--border, #E2E8F0);
      border-radius: 12px;
      padding: 18px 20px;
      display: flex;
      align-items: center;
      gap: 16px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .lang-stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .stat-icon-box {
      width: 46px;
      height: 46px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .stat-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--muted, #64748B);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      margin-bottom: 4px;
    }

    .stat-value {
      font-size: 22px;
      font-weight: 800;
      color: var(--ink, #0F172A);
      line-height: 1.1;
    }

    .lang-layout-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.85fr) minmax(0, 1.15fr);
      gap: 24px;
      align-items: start;
    }

    .form-card {
      background: var(--card-bg, #FFFFFF);
      border: 1px solid var(--border, #E2E8F0);
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
      position: sticky;
      top: 24px;
    }

    .form-card-header {
      padding: 16px 20px;
      background: #FFFFFF;
      border-bottom: 1px solid var(--border, #E2E8F0);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .form-card-body {
      padding: 22px 20px;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 700;
      color: var(--ink, #0F172A);
      margin-bottom: 6px;
    }

    .form-group .input-text,
    .form-group select.input-text {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid var(--border, #E2E8F0);
      border-radius: 8px;
      font-size: 14px;
      font-family: inherit;
      color: var(--ink, #0F172A);
      background-color: #FFFFFF;
      outline: none;
      transition: all 0.15s ease;
      box-sizing: border-box;
    }

    .form-group .input-text:focus,
    .form-group select.input-text:focus {
      border-color: var(--primary-teal, #0F766E);
      box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
      background-color: #FFFFFF;
    }

    .form-hint {
      font-size: 11px;
      color: var(--muted, #64748B);
      margin-top: 5px;
      display: block;
    }

    .alert-banner {
      border-radius: 10px;
      padding: 14px 18px;
      margin-bottom: 20px;
      font-size: 13px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .alert-success {
      background: #ECFDF5;
      border: 1px solid #A7F3D0;
      color: #065F46;
    }

    .alert-danger {
      background: #FEF2F2;
      border: 1px solid #FECACA;
      color: #991B1B;
    }

    .alert-dismiss-btn {
      margin-left: auto;
      background: none;
      border: none;
      cursor: pointer;
      color: inherit;
      opacity: 0.7;
      display: flex;
      align-items: center;
      padding: 2px;
    }

    .alert-dismiss-btn:hover {
      opacity: 1;
    }

    .badge-code {
      font-size: 12px;
      font-weight: 700;
      background: #F1F5F9;
      color: var(--ink, #0F172A);
      padding: 4px 8px;
      border-radius: 6px;
      letter-spacing: 0.5px;
      border: 1px solid var(--border, #E2E8F0);
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    .badge-default {
      background: #FEF3C7;
      color: #92400E;
      border: 1px solid #FDE68A;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 2px 7px;
      border-radius: 999px;
      font-size: 11px;
    }

    /* Pagination container */
    .table-pagination-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
      border-top: 1px solid var(--border, #E2E8F0);
      background: #FFFFFF;
      flex-wrap: wrap;
      gap: 12px;
    }

    .pagination-info {
      font-size: 13px;
      color: var(--muted, #64748B);
      font-weight: 500;
    }

    .pagination-controls {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .pag-btn {
      min-width: 32px;
      height: 32px;
      padding: 0 10px;
      border: 1px solid var(--border, #E2E8F0);
      background: #FFFFFF;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      color: var(--ink, #0F172A);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.15s ease;
      font-family: inherit;
    }

    .pag-btn:hover:not(:disabled) {
      background: #F8FAFC;
      border-color: #CBD5E1;
      color: var(--primary-teal, #0F766E);
    }

    .pag-btn.active {
      background: var(--primary-teal, #0F766E);
      border-color: var(--primary-teal, #0F766E);
      color: #FFFFFF;
    }

    .pag-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    @media (max-width: 1024px) {
      .lang-layout-grid {
        grid-template-columns: 1fr;
      }
      .form-card {
        position: static;
      }
      .lang-stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 640px) {
      .lang-stats-grid {
        grid-template-columns: 1fr;
      }
      .table-pagination-bar {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>

<body>

  <div class="admin-container">
    @include('admin.partials.admin-sidebar', ['active' => 'languages'])

    <!-- Main Content Area -->
    <main class="admin-main">
      <!-- Top Header -->
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
            <div class="breadcrumb">
              <a href="/adminorders">{{ __('Settings') }}</a>
              <span>/</span>
              <span>{{ __('Languages') }}</span>
            </div>
            <h1 class="page-heading">{{ __('Languages') }}</h1>
            <div style="font-size: 13px; color: var(--muted, #64748B);">{{ __('Configure store locales, set default language, and manage translations') }}</div>
          </div>
        </div>

        <div class="header-actions">
          <div class="search-box-wrap">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="langSearchInput" class="search-input" placeholder="{{ __('Search language, code...') }}"
              aria-label="{{ __('Search languages') }}" oninput="onSearchChange(this.value)">
          </div>

          <!-- Notification Bar with Integrated Language Switcher Dropdown -->
          @include('admin.partials.notification-bar')

          <a href="#addLanguageFormCard" class="btn-primary" onclick="focusAddLanguage(event)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            <span>{{ __('Add Language') }}</span>
          </a>
        </div>
      </header>

      <!-- Dashboard Body Content -->
      <div class="admin-content">

        <!-- Flash Messages -->
        @if (session('success'))
          <div class="alert-banner alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="flex-shrink: 0;">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
              <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            <div style="flex: 1;">{{ session('success') }}</div>
            <button type="button" class="alert-dismiss-btn" onclick="this.parentElement.remove()" aria-label="Dismiss">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </button>
          </div>
        @endif

        @if (session('error'))
          <div class="alert-banner alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="flex-shrink: 0;">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <div style="flex: 1;">{{ session('error') }}</div>
            <button type="button" class="alert-dismiss-btn" onclick="this.parentElement.remove()" aria-label="Dismiss">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </button>
          </div>
        @endif

        @if (isset($errors) && $errors->any())
          <div class="alert-banner alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="flex-shrink: 0;">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <div style="flex: 1;">
              <div style="margin-bottom: 4px; font-weight: 700;">Please resolve the following:</div>
              <ul style="margin: 0; padding-left: 18px; font-weight: 500;">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            <button type="button" class="alert-dismiss-btn" onclick="this.parentElement.remove()" aria-label="Dismiss">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </button>
          </div>
        @endif

        @php
          $totalCount = $languages->count();
          $activeCount = $languages->where('is_active', true)->count();
          $defaultLang = $languages->firstWhere('is_default', true);
        @endphp

        <!-- Metrics Row -->
        <section class="lang-stats-grid" aria-label="Languages summary">
          <div class="lang-stat-card">
            <div class="stat-icon-box" style="background: #E0F2FE; color: #0284C7;">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <line x1="2" y1="12" x2="22" y2="12" />
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
              </svg>
            </div>
            <div>
              <div class="stat-label">{{ __('Total Installed') }}</div>
              <div class="stat-value" id="statTotalCount">{{ $totalCount }}</div>
            </div>
          </div>

          <div class="lang-stat-card">
            <div class="stat-icon-box" style="background: #DCFCE7; color: #166534;">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>
            </div>
            <div>
              <div class="stat-label">{{ __('Active on Storefront') }}</div>
              <div class="stat-value">{{ $activeCount }}</div>
            </div>
          </div>

          <div class="lang-stat-card">
            <div class="stat-icon-box" style="background: #FEF3C7; color: #92400E;">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
              </svg>
            </div>
            <div>
              <div class="stat-label">{{ __('Default Locale') }}</div>
              <div class="stat-value" style="font-size: 18px;">
                {{ $defaultLang ? $defaultLang->name : 'English' }}
                <span style="font-size: 13px; font-weight: 600; color: var(--muted, #64748B);">({{ $defaultLang ? strtoupper($defaultLang->code) : 'EN' }})</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Main 2-Column Panel Layout -->
        <div class="lang-layout-grid">
          <!-- Left Column: Table of Languages -->
          <div class="lang-table-col">
            <section class="category-section-card">
              <div class="category-card-header">
                <div class="category-title-wrap">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="2" y1="12" x2="22" y2="12" />
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                  </svg>
                  <h2 class="category-title">{{ __('All Languages') }}</h2>
                  <span class="category-badge-pill" id="languageCountPill">{{ $totalCount }} {{ __('Languages') }}</span>
                </div>

                <div class="filter-pills-wrap">
                  <button type="button" class="filter-pill active" data-filter="all" onclick="filterStatus('all', this)">{{ __('All') }} ({{ $totalCount }})</button>
                  <button type="button" class="filter-pill" data-filter="active" onclick="filterStatus('active', this)">{{ __('Active') }} ({{ $activeCount }})</button>
                  <button type="button" class="filter-pill" data-filter="disabled" onclick="filterStatus('disabled', this)">{{ __('Disabled') }} ({{ $totalCount - $activeCount }})</button>
                </div>
              </div>

              <div class="table-responsive">
                <table class="products-table">
                  <thead>
                    <tr>
                      <th>{{ __('Language') }}</th>
                      <th>{{ __('Code') }}</th>
                      <th>{{ __('Default') }}</th>
                      <th>{{ __('Status') }}</th>
                      <th style="text-align: right;">{{ __('Actions') }}</th>
                    </tr>
                  </thead>
                  <tbody id="languagesTableBody">
                    @forelse ($languages as $lang)
                      @php
                        $searchPayload = strtolower(trim($lang->name . ' ' . ($lang->native_name ?? '') . ' ' . $lang->code));
                        $rowStatus = $lang->is_active ? 'active' : 'disabled';
                      @endphp
                      <tr class="lang-row" data-search="{{ $searchPayload }}" data-status="{{ $rowStatus }}">
                        <td>
                          <div class="product-meta-cell">
                            <div class="product-icon-wrap" style="color: var(--primary-teal, #0F766E); background: #E6F4F1; border-color: rgba(15,118,110,0.15); font-weight: 800; font-size: 13px; letter-spacing: 0.5px;">
                              {{ strtoupper(substr($lang->code, 0, 2)) }}
                            </div>
                            <div>
                              <div class="product-name" style="display: flex; align-items: center; gap: 8px;">
                                {{ $lang->name }}
                                @if ($lang->is_default)
                                  <span class="badge-default">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                    {{ __('Default') }}
                                  </span>
                                @endif
                              </div>
                              @if ($lang->native_name && $lang->native_name !== $lang->name)
                                <div class="product-subtitle">{{ $lang->native_name }}</div>
                              @endif
                            </div>
                          </div>
                        </td>

                        <td>
                          <code class="badge-code">{{ strtolower($lang->code) }}</code>
                        </td>

                        <td>
                          @if ($lang->is_default)
                            <span class="badge badge-default">
                              <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                              </svg>
                              {{ __('Default') }}
                            </span>
                          @else
                            <span style="color: var(--muted, #64748B); font-size: 13px; font-weight: 600;">—</span>
                          @endif
                        </td>

                        <td>
                          @if ($lang->is_active)
                            <span class="badge badge-in_stock"><span class="badge-dot"></span>{{ __('Active') }}</span>
                          @else
                            <span class="badge badge-out_of_stock"><span class="badge-dot"></span>{{ __('Disabled') }}</span>
                          @endif
                        </td>

                        <td>
                          <div class="table-actions" style="justify-content: flex-end;">
                            @if ($lang->is_default)
                              <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: var(--muted, #64748B); font-weight: 600; padding: 6px 8px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                {{ __('Primary') }}
                              </span>
                            @else
                              <!-- Set Default Button -->
                              <form method="POST" action="{{ route('admin.languages.default', $lang) }}" style="display:inline; margin:0;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="edit-btn" title="{{ __('Set Default') }}">
                                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                  </svg>
                                  {{ __('Set Default') }}
                                </button>
                              </form>

                              <!-- Toggle Active Button -->
                              <form method="POST" action="{{ route('admin.languages.toggle', $lang) }}" style="display:inline; margin:0;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="edit-btn" title="{{ $lang->is_active ? __('Disable') : __('Enable') }}">
                                  @if ($lang->is_active)
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                      <circle cx="12" cy="12" r="10" />
                                      <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                                    </svg>
                                    {{ __('Disable') }}
                                  @else
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                      <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    {{ __('Enable') }}
                                  @endif
                                </button>
                              </form>

                              <!-- Delete Button -->
                              <form method="POST" action="{{ route('admin.languages.destroy', $lang) }}" style="display:inline; margin:0;"
                                onsubmit="return confirm('Are you sure you want to delete &quot;{{ $lang->name }}&quot;? This will remove its locale setting.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" title="{{ __('Delete') }}">
                                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                  </svg>
                                  {{ __('Delete') }}
                                </button>
                              </form>
                            @endif
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr id="emptyLanguagesRow">
                        <td colspan="5" style="text-align: center; padding: 40px 16px; color: var(--muted, #64748B); font-size: 13px;">
                          {{ __('No languages installed yet.') }}
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <!-- Pagination Footer -->
              <div class="table-pagination-bar" id="paginationBar">
                <div class="pagination-info" id="paginationInfo">
                  Showing 1 to 15 of {{ $totalCount }} languages
                </div>
                <div class="pagination-controls" id="paginationControls">
                  <!-- Dynamically populated via JS -->
                </div>
              </div>
            </section>
          </div>

          <!-- Right Column: Add Language Card with Preset Selector -->
          <div class="lang-form-col" id="addLanguageFormCard">
            <section class="form-card">
              <div class="form-card-header">
                <div class="category-title-wrap">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                  </svg>
                  <h2 class="category-title">{{ __('Add New Language') }}</h2>
                </div>
                <span class="category-badge-pill">New Locale</span>
              </div>

              <div class="form-card-body">
                <!-- Preset Catalog Dropdown -->
                @if (!empty($presets))
                  <div class="form-group" style="background: #F8FAFC; border: 1px solid var(--border, #E2E8F0); border-radius: 8px; padding: 12px 14px; margin-bottom: 20px;">
                    <label for="catalogPresetSelect" style="color: var(--primary-teal, #0F766E); display: flex; align-items: center; gap: 6px;">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                      </svg>
                      {{ __('Quick-Fill from ISO Catalog') }}
                    </label>
                    <select id="catalogPresetSelect" class="input-text" onchange="applyPreset(this)">
                      <option value="">-- {{ __('Choose standard language preset...') }} --</option>
                      @foreach ($presets as $p)
                        <option value="{{ $p['code'] }}" data-name="{{ $p['name'] }}" data-native="{{ $p['native_name'] }}">
                          {{ $p['name'] }} ({{ $p['native_name'] }}) — [{{ $p['code'] }}]
                        </option>
                      @endforeach
                    </select>
                    <span class="form-hint">{{ __('Select any of the 184 ISO languages to auto-fill the form.') }}</span>
                  </div>
                @endif

                <form method="POST" action="{{ route('admin.languages.store') }}" id="addLanguageForm">
                  @csrf

                  <div class="form-group">
                    <label for="langName">
                      {{ __('Language Name') }} <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="text" id="langName" name="name" class="input-text" placeholder="e.g. Gujarati, Spanish, French"
                      value="{{ old('name') }}" maxlength="100" required>
                    <span class="form-hint">English display name (e.g. Gujarati)</span>
                  </div>

                  <div class="form-group">
                    <label for="langNative">
                      {{ __('Native Name') }}
                    </label>
                    <input type="text" id="langNative" name="native_name" class="input-text" placeholder="e.g. ગુજરાતી, Español, Français"
                      value="{{ old('native_name') }}" maxlength="100">
                    <span class="form-hint">Name written in native script (e.g. ગુજરાતી)</span>
                  </div>

                  <div class="form-group">
                    <label for="langCode">
                      {{ __('Language Code (ISO 639-1)') }} <span style="color: #DC2626;">*</span>
                    </label>
                    <input type="text" id="langCode" name="code" class="input-text" placeholder="e.g. gu, es, fr"
                      value="{{ old('code') }}" maxlength="10" required style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace;">
                    <span class="form-hint">Standard 2-letter lowercase code (e.g. gu)</span>
                  </div>

                  <div style="background: #F8FAFC; border: 1px dashed var(--border, #E2E8F0); border-radius: 8px; padding: 12px 14px; margin-bottom: 20px;">
                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-teal, #0F766E); flex-shrink: 0; margin-top: 2px;">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="16" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                      </svg>
                      <div style="font-size: 12px; color: var(--muted, #64748B); line-height: 1.45;">
                        {{ __('Adding a language automatically provisions a') }} <code>lang/{code}.json</code> {{ __('translation dictionary.') }}
                      </div>
                    </div>
                  </div>

                  <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 11px 16px; font-size: 13px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="12" y1="5" x2="12" y2="19" />
                      <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    {{ __('Add Language') }}
                  </button>
                </form>
              </div>
            </section>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Mobile Sidebar Drawer Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const adminSidebar = document.getElementById('adminSidebar');
    if (mobileMenuBtn && adminSidebar) {
      mobileMenuBtn.addEventListener('click', function () {
        adminSidebar.classList.toggle('open');
      });
    }

    // Auto-fill form from catalog preset
    function applyPreset(selectElement) {
      const selected = selectElement.options[selectElement.selectedIndex];
      if (!selected || !selected.value) return;

      const code = selected.value;
      const name = selected.dataset.name || '';
      const native = selected.dataset.native || '';

      document.getElementById('langName').value = name;
      document.getElementById('langNative').value = native;
      document.getElementById('langCode').value = code;
    }

    // Client-side filtering & pagination
    let activeFilter = 'all';
    let searchQuery = '';
    let currentPage = 1;
    const pageSize = 15;

    function filterStatus(status, pillElement) {
      activeFilter = status;
      currentPage = 1;

      document.querySelectorAll('.filter-pills-wrap .filter-pill').forEach(pill => {
        pill.classList.remove('active');
      });
      if (pillElement) {
        pillElement.classList.add('active');
      }

      renderTable();
    }

    function onSearchChange(val) {
      searchQuery = (val || '').trim().toLowerCase();
      currentPage = 1;
      renderTable();
    }

    function goToPage(page) {
      currentPage = page;
      renderTable();
    }

    function renderTable() {
      const allRows = Array.from(document.querySelectorAll('#languagesTableBody tr.lang-row'));
      if (allRows.length === 0) return;

      // 1. Filter matching rows
      const matchingRows = allRows.filter(row => {
        const rowSearch = row.dataset.search || '';
        const rowStatus = row.dataset.status || '';

        const matchesQuery = !searchQuery || rowSearch.includes(searchQuery);
        const matchesStatus = (activeFilter === 'all') || (rowStatus === activeFilter);

        return matchesQuery && matchesStatus;
      });

      const totalMatching = matchingRows.length;
      const totalPages = Math.ceil(totalMatching / pageSize) || 1;
      if (currentPage > totalPages) currentPage = totalPages;
      if (currentPage < 1) currentPage = 1;

      const startIndex = (currentPage - 1) * pageSize;
      const endIndex = startIndex + pageSize;

      // 2. Hide all rows first, then show only the ones on current page
      allRows.forEach(row => {
        row.style.display = 'none';
      });

      matchingRows.slice(startIndex, endIndex).forEach(row => {
        row.style.display = '';
      });

      // 3. Update count badge
      const countPill = document.getElementById('languageCountPill');
      if (countPill) {
        countPill.textContent = `${totalMatching} Languages`;
      }

      // 4. Update pagination info
      const info = document.getElementById('paginationInfo');
      if (info) {
        if (totalMatching === 0) {
          info.textContent = 'Showing 0 languages';
        } else {
          const from = startIndex + 1;
          const to = Math.min(endIndex, totalMatching);
          info.textContent = `Showing ${from} to ${to} of ${totalMatching} languages`;
        }
      }

      // 5. Update pagination buttons
      const controls = document.getElementById('paginationControls');
      if (controls) {
        controls.innerHTML = '';
        if (totalPages <= 1) return;

        // Prev button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pag-btn';
        prevBtn.innerHTML = '&laquo;';
        prevBtn.disabled = (currentPage === 1);
        prevBtn.onclick = () => goToPage(currentPage - 1);
        controls.appendChild(prevBtn);

        // Page buttons window
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
          startPage = Math.max(1, endPage - 4);
        }

        for (let p = startPage; p <= endPage; p++) {
          const pageBtn = document.createElement('button');
          pageBtn.className = 'pag-btn' + (p === currentPage ? ' active' : '');
          pageBtn.textContent = p;
          pageBtn.onclick = () => goToPage(p);
          controls.appendChild(pageBtn);
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pag-btn';
        nextBtn.innerHTML = '&raquo;';
        nextBtn.disabled = (currentPage === totalPages);
        nextBtn.onclick = () => goToPage(currentPage + 1);
        controls.appendChild(nextBtn);
      }
    }

    function focusAddLanguage(e) {
      e.preventDefault();
      const input = document.getElementById('catalogPresetSelect') || document.getElementById('langName');
      const card = document.getElementById('addLanguageFormCard');
      if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
      if (input) {
        setTimeout(() => input.focus(), 300);
      }
    }

    // Initial render
    document.addEventListener('DOMContentLoaded', function () {
      renderTable();
    });
  </script>
</body>

</html>
