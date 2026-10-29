{{--
  Shared Language Switcher Dropdown (Beside Notification Bar)
--}}
@php
  $activeLanguages = $activeLanguages ?? \App\Models\Language::active();
  $currentLocale = app()->getLocale();
  $currentLang = $activeLanguages->firstWhere('code', $currentLocale) 
      ?? $activeLanguages->firstWhere('is_default', true) 
      ?? $activeLanguages->first();
@endphp

<div class="lang-switcher-wrap" id="langSwitcherWrap">
  <button type="button" class="lang-switcher-btn" id="langSwitcherBtn" aria-label="{{ __('Select Language') }}" aria-expanded="false" aria-haspopup="true">
    <svg class="lang-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10" />
      <line x1="2" y1="12" x2="22" y2="12" />
      <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
    </svg>
    <span class="lang-btn-label">
      <span class="lang-btn-text">{{ $currentLang ? ($currentLang->native_name ?: $currentLang->name) : 'Language' }}</span>
      <span class="lang-code-pill">{{ strtoupper($currentLocale) }}</span>
    </span>
    <svg class="lang-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="6 9 12 15 18 9" />
    </svg>
  </button>

  <div class="lang-panel" id="langPanel" role="dialog" aria-label="{{ __('Select Language') }}">
    <div class="lang-panel-header">
      <span class="lang-panel-title">{{ __('Language') }}</span>
      <span class="lang-panel-count">{{ $activeLanguages->count() }} {{ __('Active') }}</span>
    </div>

    <div class="lang-list">
      @foreach($activeLanguages as $lang)
        @php
          $isSelected = strtolower($lang->code) === strtolower($currentLocale);
        @endphp
        <form method="POST" action="{{ route('admin.languages.switch', $lang->code) }}" class="lang-option-form">
          @csrf
          <button type="submit" class="lang-option-btn {{ $isSelected ? 'active' : '' }}" title="{{ $lang->name }}">
            <span class="lang-option-code">{{ strtoupper(substr($lang->code, 0, 3)) }}</span>
            <span class="lang-option-meta">
              <span class="lang-option-name">{{ $lang->name }}</span>
              @if($lang->native_name && $lang->native_name !== $lang->name)
                <span class="lang-option-native">{{ $lang->native_name }}</span>
              @endif
            </span>
            @if($isSelected)
              <svg class="lang-check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            @endif
          </button>
        </form>
      @endforeach
    </div>

    <div style="padding: 8px 12px; border-top: 1px solid var(--border, #E2E8F0); background: #F8FAFC; text-align: center;">
      <a href="/adminlanguages" style="font-size: 11px; font-weight: 700; color: var(--primary-teal, #0F766E); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="3"></circle>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
        {{ __('Manage All 184 Languages') }} &rarr;
      </a>
    </div>
  </div>
</div>

<script>
  (function () {
    var wrap = document.getElementById('langSwitcherWrap');
    if (!wrap) return;

    var btn = document.getElementById('langSwitcherBtn');
    var panel = document.getElementById('langPanel');

    function closeLangPanel() {
      panel.classList.remove('open');
      wrap.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }

    btn.addEventListener('click', function (event) {
      event.stopPropagation();

      // If notification panel is open, close it
      var ntfPanel = document.getElementById('ntfPanel');
      if (ntfPanel && ntfPanel.classList.contains('open')) {
        ntfPanel.classList.remove('open');
      }

      var isOpen = panel.classList.toggle('open');
      wrap.classList.toggle('open', isOpen);
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
      if (!wrap.contains(event.target)) {
        closeLangPanel();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeLangPanel();
      }
    });
  })();
</script>
