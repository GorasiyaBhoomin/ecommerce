{{--
  Shared admin sidebar.
  Params:
    $active       - active nav key: orders | products | add-product | import-products | languages | categories | variants | users | profile
    $showUserPill - (bool) render the user pill in the footer (defaults to false)
--}}
@php
  $active = $active ?? '';
  $showUserPill = $showUserPill ?? false;
@endphp
    <!-- Left Dark Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="brand-area">
        <div class="brand-logo-icon">
          <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
            <path d="M3 6h18" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
        </div>
        <div>
          <div class="brand-title">Shopkart</div>
          <span class="brand-badge">{{ __('Admin Suite') }}</span>
        </div>
      </div>

      <div class="nav-label">{{ __('Management') }}</div>
      <ul class="sidebar-menu">
        <li>
          <a href="/adminorders" class="menu-link {{ $active === 'orders' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
              <path d="m14 9 3 3-3 3" />
            </svg>
            {{ __('Orders') }}
          </a>
        </li>
        <li>
          <a href="/adminproducts" class="menu-link {{ $active === 'products' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m7.5 4.27 9 5.15" />
              <path
                d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
              <path d="m3.3 7 8.7 5 8.7-5" />
              <path d="M12 22V12" />
            </svg>
            {{ __('Products & Stock') }}
          </a>
        </li>
        <li>
          <a href="/adminaddproduct" class="menu-link {{ $active === 'add-product' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <path d="M8 12h8" />
              <path d="M12 8v8" />
            </svg>
            {{ __('Add Product') }}
          </a>
        </li>
        <li>
          <a href="/adminimportproducts" class="menu-link {{ $active === 'import-products' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            {{ __('Bulk Import') }}
          </a>
        </li>
        <li>
          <a href="/adminlanguages" class="menu-link {{ $active === 'languages' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <path d="M2 12h20" />
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
            </svg>
            {{ __('Languages') }}
          </a>
        </li>
        <li>
          <a href="/admincategories" class="menu-link {{ $active === 'categories' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
            </svg>
            {{ __('Categories') }}
          </a>
        </li>
        <li>
          <a href="/adminvariants" class="menu-link {{ $active === 'variants' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="4" y1="21" x2="4" y2="14" />
              <line x1="4" y1="10" x2="4" y2="3" />
              <line x1="12" y1="21" x2="12" y2="12" />
              <line x1="12" y1="8" x2="12" y2="3" />
              <line x1="20" y1="21" x2="20" y2="16" />
              <line x1="20" y1="12" x2="20" y2="3" />
              <line x1="1" y1="14" x2="7" y2="14" />
              <line x1="9" y1="8" x2="15" y2="8" />
              <line x1="17" y1="16" x2="23" y2="16" />
            </svg>
            {{ __('Variants') }}
          </a>
        </li>
        <li>
          <a href="/adminusers" class="menu-link {{ $active === 'users' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            {{ __('Users') }}
          </a>
        </li>
        <li>
          <a href="/adminprofile" class="menu-link {{ $active === 'profile' ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="3" rx="2" />
              <path d="M9 3v18" />
              <path d="m14 9 3 3-3 3" />
            </svg>
            {{ __('Profile') }}
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        @if ($showUserPill)
          @php
            $sidebarAdmin = $admin ?? (session('user_id') ? \App\Models\users::find(session('user_id')) : null);
          @endphp
          @if ($sidebarAdmin)
            <div class="user-pill">
              <div class="user-avatar">{{ strtoupper(substr($sidebarAdmin->name, 0, 1)) }}</div>
              <div class="user-info">
                <div class="user-name">{{ $sidebarAdmin->name }}</div>
                <div class="user-role">{{ $sidebarAdmin->email }}</div>
              </div>
            </div>
          @endif
        @endif
      </div>
    </aside>
