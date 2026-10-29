<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $product->name }} | Shopkart</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/products.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/user-navbar.css') }}">
</head>

<body>

  @include('user.partials.navbar', ['active' => 'home'])

  @php
    $categoryName = $product->category->name ?? 'Uncategorised';

    $defaultPrice = $defaultVariant['price'] ?? null;
    $defaultMrp = $defaultVariant['actual_price'] ?? null;
    $defaultStock = $defaultVariant['stock_qty'] ?? 0;
    $defaultDiscount = ($defaultPrice !== null && $defaultMrp > $defaultPrice)
        ? (int) round(($defaultMrp - $defaultPrice) / $defaultMrp * 100)
        : null;

    if ($defaultStock > 5) {
        $defaultStockClass = 'stock-in';
        $defaultStockMessage = 'In stock ('.$defaultStock.' units available)';
    } elseif ($defaultStock > 0) {
        $defaultStockClass = 'stock-low';
        $defaultStockMessage = 'Only '.$defaultStock.' left in stock - Order soon';
    } else {
        $defaultStockClass = 'stock-out';
        $defaultStockMessage = 'Out of stock - Currently unavailable';
    }
  @endphp

  <!-- Main Content -->
  <main class="main-content">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
      <a href="{{ route('shop.index') }}">Home</a>
      <span>/</span>
      <a href="{{ route('shop.index', ['category' => $product->category->slug ?? '']) }}">{{ $categoryName }}</a>
      <span>/</span>
      <span style="color: var(--ink); font-weight: 600;">{{ $product->name }}</span>
    </nav>

    <!-- Product Detail Container -->
    <div class="product-detail-container">
      <!-- Left Column: Image & Perks -->
      <div class="gallery-column">
        <div class="main-image-viewport">
          @if (! empty($images))
            <img id="mainProductImg" src="{{ $images[0] }}" alt="{{ $product->name }}">
          @else
            <div class="image-placeholder">
              <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <rect width="14" height="20" x="5" y="2" rx="2" />
                <path d="M12 18h.01" />
              </svg>
            </div>
          @endif
        </div>

        @if (count($images) > 1)
          <div class="thumbnail-strip" id="thumbnailStrip" aria-label="Product image gallery">
            @foreach ($images as $index => $image)
              <button type="button" class="thumbnail-item {{ $index === 0 ? 'active' : '' }}"
                onclick="selectImage({{ $index }})" aria-label="View {{ $product->name }} image {{ $index + 1 }}">
                <img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}">
              </button>
            @endforeach
          </div>
        @endif

        <div class="gallery-perks">
          <div class="perk-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            <span>1 Year Brand Warranty</span>
          </div>
          <div class="perk-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="16" height="13" x="1" y="6" rx="2" />
              <circle cx="5.5" cy="18.5" r="2.5" />
              <circle cx="12.5" cy="18.5" r="2.5" />
            </svg>
            <span>Doorstep Fast Delivery</span>
          </div>
          <div class="perk-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="23 4 23 10 17 10" />
              <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
            </svg>
            <span>7-Day Easy Returns</span>
          </div>
        </div>
      </div>

      <!-- Right Column: Product Configurator -->
      <div class="details-column">
        @if (session('success'))
          <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
              <span>{{ session('success') }}</span>
            </div>
            <a href="{{ route('cart.index') }}" style="color: #065F46; text-decoration: underline; font-weight: 700;">View Cart &rarr;</a>
          </div>
        @endif

        @if (session('error'))
          <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 14px; font-weight: 600;">
            {{ session('error') }}
          </div>
        @endif

        <div>
          <span class="category-badge-chip">{{ $categoryName }}</span>
          <h1 class="product-title">{{ $product->name }}</h1>
        </div>

        <!-- Live Price Display -->
        <div class="price-container">
          <span class="live-price" id="livePriceText">
            @if ($defaultPrice !== null)
              &#8377;{{ number_format($defaultPrice, 0) }}
            @else
              &mdash;
            @endif
          </span>
          <span class="mrp-strike" id="mrpText" @if ($defaultDiscount === null) style="display: none;" @endif>
            &#8377;{{ number_format($defaultMrp ?? 0, 0) }}
          </span>
          <span class="discount-pill" id="discountText" @if ($defaultDiscount === null) style="display: none;" @endif>
            Save {{ $defaultDiscount ?? 0 }}%
          </span>
        </div>

        <!-- Stock Status Alert -->
        <div class="stock-message-box {{ $defaultStockClass }}" id="stockStatusBox">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
          </svg>
          <span id="stockMessageText">{{ $defaultStockMessage }}</span>
        </div>

        <!-- Dynamic Variant Selectors -->
        <div class="variant-selectors-wrap" id="variantSelectors">
          <!-- Rendered by JS -->
        </div>
<form action="{{ route('cart.add') }}"
      method="POST"
      id="addToCartForm"
      onsubmit="handleAddToCart()">

    @csrf

    <input type="hidden"
           name="product_id"
           value="{{ $product->id }}">

    <input type="hidden"
           name="variant_id"
           id="selectedVariantId"
           value="{{ $defaultVariant['id'] ?? ($product->variants->first()?->id ?? '') }}">

    <input type="hidden"
           name="quantity"
           id="cartQuantity"
           value="1">

    <div class="purchase-actions-wrap">

        <div class="qty-control-box">

            <button class="qty-btn"
                    type="button"
                    onclick="adjustQty(-1)">
                &minus;
            </button>

            <input type="text"
                   class="qty-display"
                   id="productQty"
                   value="1"
                   readonly>

            <button class="qty-btn"
                    type="button"
                    onclick="adjustQty(1)">
                +
            </button>

        </div>

        <button type="submit"
                class="btn-add-cart"
                id="btnAddToCart"
                @if ($defaultStock <= 0) disabled @endif>

            <span id="addToCartText">
                {{ $defaultStock <= 0 ? 'Out of Stock' : 'Add to Cart' }}
            </span>

        </button>

        <a href="/checkout"
           class="btn-buy-now {{ $defaultStock <= 0 ? 'disabled' : '' }}"
           id="btnBuyNow">
            Buy Now &bull; Cash on Delivery
        </a>

    </div>

</form>
        <!-- Toast Feedback -->
        <div class="added-toast" id="addedToast">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M20 6 9 17l-5-5" />
          </svg>
          <span id="toastCartMsg">Added to your cart!</span>
        </div>

        <!-- Specs Overview -->
        <div class="specs-card">
          <h2 class="specs-title">Product Details</h2>
          <table class="specs-table">
            <tbody>
              <tr>
                <td>Product Code</td>
                <td>{{ $product->sku ?? $product->slug }}</td>
              </tr>
              <tr>
                <td>Category</td>
                <td>{{ $categoryName }}</td>
              </tr>
              <tr>
                <td>Configured Variant</td>
                <td id="specVariant">&mdash;</td>
              </tr>
              <tr>
                <td>Warranty</td>
                <td>1 Year Comprehensive Brand Warranty</td>
              </tr>
              <tr>
                <td>Shipping</td>
                <td>Free Delivery on orders above &#8377;499</td>
              </tr>
            </tbody>
          </table>
        </div>

        @if ($product->description)
          <div class="specs-card">
            <h2 class="specs-title">About this product</h2>
            <p style="font-size: 14px; color: var(--muted); line-height: 1.7; margin: 0;">{{ $product->description }}</p>
          </div>
        @endif

        <!-- Technical Specifications -->
        @if (! empty($specifications))
          <section class="techspecs-card" aria-labelledby="techSpecsTitle">
            <h2 class="techspecs-title" id="techSpecsTitle">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <line x1="10" y1="9" x2="8" y2="9" />
              </svg>
              Technical Specifications
            </h2>
            <div class="techspecs-grid">
              <div class="techspec-group">
                <div class="techspec-group-title">Specifications</div>
                <div class="techspec-list">
                  @foreach ($specifications as $specification)
                    <div class="techspec-row">
                      <span class="techspec-label">{{ $specification['label'] }}</span>
                      <span class="techspec-value">{{ $specification['value'] ?: '—' }}</span>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </section>
        @endif
      </div>
    </div>
  </main>

  <script>
    const productImages = @json($images);
    const productName = @json($product->name);
    const product = @json($payload);

    const selectedValues = {};
    let currentQty = 1;

    product.selected.forEach(function (valueId) {
      const group = product.groups.find(function (candidate) {
        return candidate.values.some(function (value) { return value.id === valueId; });
      });

      if (group) {
        selectedValues[group.id] = valueId;
      }
    });

    function escapeHtml(value) {
      return String(value).replace(/[&<>"']/g, function (character) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character];
      });
    }

    function formatPrice(amount) {
      return Number(amount).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function selectedIds(exclude) {
      return product.groups
        .filter(function (group) { return group.id !== exclude; })
        .map(function (group) { return selectedValues[group.id]; })
        .filter(function (valueId) { return typeof valueId === 'number'; })
        .sort(function (a, b) { return a - b; });
    }

    function variantFor(ids) {
      if (ids.length !== product.groups.length) {
        return Object.values(product.variants)[0] || null;
      }

      return product.variants['v' + ids.join('-')] || Object.values(product.variants)[0] || null;
    }

    function currentVariant() {
      return variantFor(selectedIds());
    }

    function optionAvailable(groupId, valueId) {
      const ids = selectedIds(groupId).concat([valueId]).sort(function (a, b) { return a - b; });
      const variant = variantFor(ids);

      return variant !== null && variant.stock_qty > 0;
    }

    function selectedLabel(group) {
      const value = group.values.find(function (candidate) { return candidate.id === selectedValues[group.id]; });

      return value ? value.value : '';
    }

    function renderSelectors() {
      const wrap = document.getElementById('variantSelectors');

      if (! product.groups.length) {
        wrap.innerHTML = '<p class="variant-empty-msg">This product has no configurable variants.</p>';
        return;
      }

      wrap.innerHTML = product.groups.map(function (group) {
        const options = group.values.map(function (value) {
          const active = selectedValues[group.id] === value.id;
          const available = optionAvailable(group.id, value.id);

          if (group.is_color) {
            return '<button type="button" class="color-swatch-btn' + (active ? ' active' : '') + (available ? '' : ' sold-out') +
              '" onclick="selectValue(' + group.id + ', ' + value.id + ')" title="' + escapeHtml(value.value) + (available ? '' : ' (Sold out)') + '">' +
              '<span class="color-swatch-circle" style="background: ' + escapeHtml(value.hex) + ';"></span>' +
              '<span>' + escapeHtml(value.value) + '</span>' +
              (available ? '' : '<span class="sold-out-badge">Out</span>') +
              '</button>';
          }

          return '<button type="button" class="ram-pill-btn' + (active ? ' active' : '') + (available ? '' : ' sold-out') +
            '" onclick="selectValue(' + group.id + ', ' + value.id + ')"' + (available ? '' : ' disabled') + '>' +
            '<span class="ram-size-text">' + escapeHtml(value.value) + '</span>' +
            (available ? '' : '<span class="ram-price-text">Sold out</span>') +
            '</button>';
        }).join('');

        return '<div class="selector-block">' +
          '<div class="selector-label">' +
          '<span>Select ' + escapeHtml(group.name) + '</span>' +
          '<span class="selector-label-val">' + escapeHtml(selectedLabel(group)) + '</span>' +
          '</div>' +
          '<div class="' + (group.is_color ? 'color-swatches-wrap' : 'ram-options-wrap') + '">' + options + '</div>' +
          '</div>';
      }).join('');
    }

    function selectValue(groupId, valueId) {
      selectedValues[groupId] = valueId;
      renderSelectors();
      updateVariantState();
    }

    function updateVariantState() {
      const variant = currentVariant();

      const priceText = document.getElementById('livePriceText');
      const mrpText = document.getElementById('mrpText');
      const discountText = document.getElementById('discountText');
      const stockBox = document.getElementById('stockStatusBox');
      const stockMessage = document.getElementById('stockMessageText');
      const addToCartButton = document.getElementById('btnAddToCart');
      const buyNowButton = document.getElementById('btnBuyNow');
      const addToCartText = document.getElementById('addToCartText');

      if (! variant) {
        const fallback = Object.values(product.variants)[0] || null;
        if (fallback) {
          variant = fallback;
        }
      }

      if (! variant) {
        document.getElementById('selectedVariantId').value = '';
        priceText.innerHTML = '&mdash;';
        mrpText.style.display = 'none';
        discountText.style.display = 'none';
        stockBox.className = 'stock-message-box stock-out';
        stockMessage.textContent = 'Out of stock - this combination is unavailable';
        addToCartButton.disabled = true;
        buyNowButton.classList.add('disabled');
        addToCartText.textContent = 'Out of Stock';
        document.getElementById('specVariant').textContent = 'Unavailable combination';
        return;
      }

      priceText.innerHTML = '&#8377;' + formatPrice(variant.price);
      document.getElementById('selectedVariantId').value = variant.id;

      if (variant.actual_price > variant.price) {
        mrpText.style.display = '';
        mrpText.innerHTML = '&#8377;' + formatPrice(variant.actual_price);
        discountText.style.display = '';
        discountText.textContent = 'Save ' + Math.round(((variant.actual_price - variant.price) / variant.actual_price) * 100) + '%';
      } else {
        mrpText.style.display = 'none';
        discountText.style.display = 'none';
      }

      if (variant.stock_qty > product.lowStockThreshold) {
        stockBox.className = 'stock-message-box stock-in';
        stockMessage.textContent = 'In stock (' + variant.stock_qty + ' units available)';
      } else if (variant.stock_qty > 0) {
        stockBox.className = 'stock-message-box stock-low';
        stockMessage.textContent = 'Only ' + variant.stock_qty + ' left in stock - Order soon';
      } else {
        stockBox.className = 'stock-message-box stock-out';
        stockMessage.textContent = 'Out of stock - Currently unavailable';
      }

      const inStock = variant.stock_qty > 0;
      addToCartButton.disabled = ! inStock;
      buyNowButton.classList.toggle('disabled', ! inStock);
      addToCartText.textContent = inStock ? 'Add to Cart' : 'Out of Stock';

      document.getElementById('specVariant').textContent = product.groups
        .map(function (group) { return selectedLabel(group); })
        .filter(function (label) { return label !== ''; })
        .join(' • ');
    }

    function selectImage(index) {
      if (! productImages.length || index < 0 || index >= productImages.length) {
        return;
      }

      const mainImage = document.getElementById('mainProductImg');

      if (mainImage) {
        mainImage.src = productImages[index];
      }

      document.querySelectorAll('#thumbnailStrip .thumbnail-item').forEach(function (thumbnail, position) {
        thumbnail.classList.toggle('active', position === index);
      });
    }

  function adjustQty(delta) {

    const variant = currentVariant();

    if (!variant || variant.stock_qty <= 0) {
        return;
    }

    const maxStock = Number(variant.stock_qty);

    currentQty = Math.min(
        maxStock,
        Math.max(1, currentQty + delta)
    );

    document.getElementById('productQty').value = currentQty;
    document.getElementById('cartQuantity').value = currentQty;

    // Disable plus button when maximum stock reached
    document.getElementById('qtyPlus').disabled =
        currentQty >= maxStock;

    // Disable minus button when quantity is 1
    document.getElementById('qtyMinus').disabled =
        currentQty <= 1;
}

    function handleAddToCart() {
      const variant = currentVariant();
      const toast = document.getElementById('addedToast');
      const message = document.getElementById('toastCartMsg');

      if (! variant || ! toast || ! message) {
        return;
      }

      const configuration = product.groups
        .map(function (group) { return selectedLabel(group); })
        .filter(function (label) { return label !== ''; })
        .join(' ');

      message.textContent = currentQty + 'x ' + productName + ' ' + (configuration ? '(' + configuration + ') ' : '') + 'added to your cart!';
      toast.style.display = 'flex';
      setTimeout(function () { toast.style.display = 'none'; }, 3000);
    }

    if (productImages.length) {
      selectImage(0);
    }

    renderSelectors();
    updateVariantState();
  </script>
</body>

</html>

