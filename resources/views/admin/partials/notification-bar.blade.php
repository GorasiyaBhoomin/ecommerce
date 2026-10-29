{{--
  Admin Header Controls: Language Switcher + Notification Bell
  Included in the header of every admin page.
--}}
<div class="admin-header-controls">
  @include('admin.partials.language-switcher')

  <div class="ntf-wrap" id="ntfWrap">
    <button type="button" class="ntf-btn" id="ntfBtn" aria-label="{{ __('New orders notifications') }}" aria-expanded="false"
      aria-haspopup="true">
      <svg class="ntf-bell-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
      </svg>
      @if($newOrders->count() > 0)
      <span class="ntf-badge">{{ $newOrders->count() }}</span>
      @endif
    </button>

    <div class="ntf-panel" id="ntfPanel" role="dialog" aria-label="{{ __('New orders notifications') }}">
      <div class="ntf-panel-header">
        <span class="ntf-panel-title">{{ __('New Orders') }}</span>
        <span class="ntf-panel-count">{{ $newOrders->count() }} {{ __('new') }}</span>
      </div>

      <div class="ntf-list">
        @forelse($newOrders as $order)
        <a href="{{ route('admin.orders.show', $order->id) }}" class="ntf-item unread">
          <span class="ntf-item-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
              <path d="M3 6h18" />
              <path d="M16 10a4 4 0 0 1-8 0" />
            </svg>
          </span>
          <span class="ntf-item-body">
            <span class="ntf-item-title">{{ __('Order') }} #SK-{{ $order->id }}</span>
            <span class="ntf-item-text">{{ $order->customer_name }} &bull; {{ $order->items_count }} {{ Str::plural('item', $order->items_count) }} &bull; &#8377;{{ number_format($order->total_amount, 2) }}</span>
            <span class="ntf-item-time">{{ $order->created_at->diffForHumans() }}</span>
          </span>
          <span class="ntf-item-dot" title="Unread"></span>
        </a>
        @empty
        <div class="ntf-empty" style="padding: 20px; text-align: center; color: #64748b; font-size: 13px;">
          {{ __('No new orders received.') }}
        </div>
        @endforelse
      </div>

      <a href="/adminorders" class="ntf-view-all">{{ __('View all orders') }}</a>
    </div>
  </div>
</div>

<script>
  (function () {
    var wrap = document.getElementById('ntfWrap');
    if (!wrap) {
      return;
    }
    var btn = document.getElementById('ntfBtn');
    var panel = document.getElementById('ntfPanel');

    function closePanel() {
      panel.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    }

    btn.addEventListener('click', function (event) {
      event.stopPropagation();

      // If language panel is open, close it
      var langPanel = document.getElementById('langPanel');
      var langWrap = document.getElementById('langSwitcherWrap');
      if (langPanel && langPanel.classList.contains('open')) {
        langPanel.classList.remove('open');
        if (langWrap) langWrap.classList.remove('open');
      }

      var isOpen = panel.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
      if (!wrap.contains(event.target)) {
        closePanel();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closePanel();
      }
    });
  })();
</script>
