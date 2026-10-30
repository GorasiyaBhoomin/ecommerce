<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Bulk Import Products | Shopkart Admin</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="{{ asset('assets/css/adminaddproduct.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
  <style>
    .import-dropzone {
      border: 2px dashed #CBD5E1;
      border-radius: 12px;
      padding: 32px 24px;
      text-align: center;
      background: #F8FAFC;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
    }
    .import-dropzone:hover, .import-dropzone.dragover {
      border-color: var(--primary-teal);
      background: var(--light-teal);
    }
    .dropzone-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: #E2E8F0;
      color: var(--primary-teal);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
    }
    .import-dropzone:hover .dropzone-icon {
      background: #FFFFFF;
    }
    .dropzone-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 4px;
    }
    .dropzone-hint {
      font-size: 12px;
      color: var(--muted);
    }
    .file-input-hidden {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
    }
    .selected-file-preview {
      display: none;
      align-items: center;
      justify-content: space-between;
      margin-top: 14px;
      padding: 10px 14px;
      background: #FFFFFF;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 13px;
    }
    .selected-file-name {
      font-weight: 600;
      color: var(--ink);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .col-badge-req {
      background: #FEE2E2;
      color: #991B1B;
      font-size: 11px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 4px;
    }
    .col-badge-opt {
      background: #E2E8F0;
      color: #475569;
      font-size: 11px;
      font-weight: 600;
      padding: 2px 7px;
      border-radius: 4px;
    }
    .spec-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      margin-top: 12px;
    }
    .spec-table th, .spec-table td {
      padding: 8px 12px;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }
    .spec-table th {
      background: #F8FAFC;
      font-weight: 700;
      color: var(--ink);
    }
    .btn-sample-download {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 16px;
      background: #FFFFFF;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      color: var(--ink);
      text-decoration: none;
      transition: all 0.15s ease;
    }
    .btn-sample-download:hover {
      background: #F1F5F9;
      border-color: #CBD5E1;
    }
  </style>
</head>

<body>

  <div class="admin-container">
    <!-- Left Sidebar -->
    @include('admin.partials.admin-sidebar', ['active' => 'import-products'])

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
            <div class="breadcrumb">
              <a href="/adminproducts">Products</a>
              <span>/</span>
              <span>Bulk CSV Import</span>
            </div>
            <h1 class="page-heading">Bulk Import Products (Category-Wise)</h1>
          </div>
        </div>
        <div class="header-actions">
          
          <a href="/adminproducts" class="btn-draft">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m15 18-6-6 6-6" />
            </svg>
            Back to Catalog
          </a>
          @include('admin.partials.notification-bar')
        </div>
      </header>

      {{-- Flash Messages --}}
      @if (session('success') || session('warning') || session('error') || $errors->any() || session('import_errors'))
        <div style="padding: 20px 28px 0;">
          @if (session('success'))
            <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 10px; padding: 14px 18px; margin-bottom: 16px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M20 6 9 17l-5-5" />
              </svg>
              <span>{{ session('success') }}</span>
            </div>
          @endif

          @if (session('warning'))
            <div style="background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; border-radius: 10px; padding: 14px 18px; margin-bottom: 16px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
              </svg>
              <span>{{ session('warning') }}</span>
            </div>
          @endif

          @if (session('error'))
            <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 10px; padding: 14px 18px; margin-bottom: 16px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10" />
                <line x1="15" y1="9" x2="9" y2="15" />
                <line x1="9" y1="9" x2="15" y2="15" />
              </svg>
              <span>{{ session('error') }}</span>
            </div>
          @endif

          @if ($errors->any())
            <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 10px; padding: 14px 18px; margin-bottom: 16px; font-size: 13px; font-weight: 600;">
              <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $msg)
                  <li>{{ $msg }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (session('import_errors') && is_array(session('import_errors')))
            <div style="background: #FFF1F2; border: 1px solid #FFE4E6; color: #BE123C; border-radius: 10px; padding: 14px 18px; margin-bottom: 16px; font-size: 12px;">
              <strong style="font-size: 13px; display: block; margin-bottom: 6px;">Row Validation Notes:</strong>
              <ul style="margin: 0; padding-left: 20px; max-height: 180px; overflow-y: auto;">
                @foreach (session('import_errors') as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      @endif

      <!-- Import Form -->
      <form class="content-inner" method="POST" action="{{ route('admin.products.import.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-sections-col" style="max-width: 860px; margin: 0 auto; width: 100%;">
          <!-- Section 1: Target Category & CSV File -->
          <section class="form-card">
            <div class="card-header">
              <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                  <polyline points="7 10 12 15 17 10" />
                  <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Import Configuration
              </h2>
            </div>
            <div class="card-body">
              <!-- Category Dropdown -->
              <div class="form-group" style="margin-bottom: 24px;">
                <label for="categorySelect" style="display: block; font-weight: 700; margin-bottom: 6px;">
                  Target Category *
                </label>
                <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">
                  Choose the category where all products in your CSV file will be cataloged.
                </div>
                <select id="categorySelect" name="category_id" class="select-input" required style="width: 100%;">
                  <option value="">-- Choose Category --</option>
                  @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string) old('category_id', $selectedCategoryId) === (string) $cat->id ? 'selected' : '' }}>
                      {{ $cat->name }} ({{ $cat->products_count ?? $cat->products()->count() }} products currently)
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- CSV File Upload -->
              <div class="form-group">
                <label style="display: block; font-weight: 700; margin-bottom: 6px;">
                  Select CSV File *
                </label>
                <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">
                  Upload a standard comma-separated values (.csv) file.
                </div>

                <div class="import-dropzone" id="dropzone">
                  <input type="file" name="csv_file" id="csvFileInput" class="file-input-hidden" accept=".csv,text/csv" required>
                  <div class="dropzone-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                      <polyline points="14 2 14 8 20 8" />
                      <line x1="12" y1="18" x2="12" y2="12" />
                      <line x1="9" y1="15" x2="12" y2="12" />
                      <line x1="15" y1="15" x2="12" y2="12" />
                    </svg>
                  </div>
                  <div class="dropzone-title" id="dropzoneTitle">Click to choose a CSV file or drag &amp; drop here</div>
                  <div class="dropzone-hint">Supports .csv files up to 10MB</div>
                </div>

                <div class="selected-file-preview" id="filePreview">
                  <span class="selected-file-name">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-teal)" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                      <polyline points="14 2 14 8 20 8" />
                    </svg>
                    <span id="previewFileName">filename.csv</span>
                    <span style="color: var(--muted); font-size: 12px;" id="previewFileSize">(0 KB)</span>
                  </span>
                  <button type="button" id="removeFileBtn" style="background: none; border: none; color: #DC2626; cursor: pointer; font-size: 12px; font-weight: 600;">Remove</button>
                </div>
              </div>

              <!-- Default Images Upload (Optional) -->
              <div class="form-group" style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed var(--border);">
                <label for="defaultImagesInput" style="display: block; font-weight: 700; margin-bottom: 6px;">
                  Default Product Images (Optional)
                </label>
                <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">
                  Upload images to attach to products that don't have an <code>image</code> column specified in your CSV. You can select multiple images.
                </div>
                <input type="file" name="default_images[]" id="defaultImagesInput" class="input-text" multiple accept="image/png,image/jpeg,image/webp" style="padding: 10px; width: 100%; background: #FFFFFF;">
                <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Supports JPG, PNG, WEBP up to 5MB each.</div>
              </div>
            </div>
          </section>

      

          <!-- Submit Buttons -->
          <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
            <a href="/adminproducts" class="btn-draft" style="text-decoration: none;">
              Cancel
            </a>
            <button type="submit" class="btn-publish" id="submitBtn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
              </svg>
              Upload &amp; Bulk Import
            </button>
          </div>
        </div>
      </form>
    </main>
  </div>

  <script>
    (function () {
      'use strict';

      var fileInput = document.getElementById('csvFileInput');
      var dropzone = document.getElementById('dropzone');
      var filePreview = document.getElementById('filePreview');
      var previewFileName = document.getElementById('previewFileName');
      var previewFileSize = document.getElementById('previewFileSize');
      var removeFileBtn = document.getElementById('removeFileBtn');

      function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
      }

      function updateFileDisplay(file) {
        if (file) {
          previewFileName.textContent = file.name;
          previewFileSize.textContent = '(' + formatBytes(file.size) + ')';
          filePreview.style.display = 'flex';
          dropzone.style.display = 'none';
        } else {
          filePreview.style.display = 'none';
          dropzone.style.display = 'block';
          fileInput.value = '';
        }
      }

      fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files[0]) {
          updateFileDisplay(fileInput.files[0]);
        }
      });

      removeFileBtn.addEventListener('click', function () {
        updateFileDisplay(null);
      });

      // Drag & Drop
      ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (e) {
          e.preventDefault();
          e.stopPropagation();
          dropzone.classList.add('dragover');
        });
      });

      ['dragleave', 'drop'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (e) {
          e.preventDefault();
          e.stopPropagation();
          dropzone.classList.remove('dragover');
        });
      });

      dropzone.addEventListener('drop', function (e) {
        var dt = e.dataTransfer;
        var files = dt.files;
        if (files && files.length > 0) {
          fileInput.files = files;
          updateFileDisplay(files[0]);
        }
      });

      // Mobile Menu
      var mobileBtn = document.getElementById('mobileMenuBtn');
      var sidebar = document.getElementById('adminSidebar');
      if (mobileBtn && sidebar) {
        mobileBtn.addEventListener('click', function () {
          sidebar.classList.toggle('mobile-open');
        });
      }
    })();
  </script>
</body>

</html>
