<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Login | Shopkart</title>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>
<body>

  <div class="auth-container">
    <!-- Left Brand Panel -->
    <div class="brand-panel">
      <div class="panel-top">
        <a href="/index" class="brand-logo-row">
          <div class="brand-icon-box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
              <path d="M3 6h18"/>
              <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
          </div>
          <span class="brand-title">Shopkart</span>
        </a>

        <h2 class="panel-headline">
          Gujarat's trusted hub for electronics and farm groceries.
        </h2>

        <ul class="feature-list">
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Instant Order Tracking:</strong> Real-time 5-stage live dispatch updates from Received to Delivered.</div>
          </li>
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Flexible Variants:</strong> Pick exact RAM, Color, Weight or Volume combinations with live pricing.</div>
          </li>
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>COD &amp; Secure Payments:</strong> Pay comfortably on doorstep delivery across Gujarat.</div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Right Form Card -->
    <div class="form-column">
      <div class="auth-panel-head">
        <h1 class="auth-panel-title">Welcome back</h1>
        <p class="auth-panel-sub">Sign in to track your orders, manage your cart and check out faster.</p>
      </div>

      @if (session('success'))
        <div class="auth-alert auth-alert-success">
          {{ session('success') }}
        </div>
      @endif

      @if (session('error'))
        <div class="auth-alert" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 600;">
          {{ session('error') }}
        </div>
      @endif

      <form class="auth-form" action="{{url('/login')}}" method="post" >
        @csrf
        <div class="form-group">
          <label class="form-label" for="loginEmail">Email or Mobile Number</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="20" height="16" x="2" y="4" rx="2"/>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
            <input type="text" name="email" class="input-field"  placeholder="name@example.com" >
              @error('email')
        <span style="color:red">{{ $message }}</span>
    @enderror
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="loginPass">Password</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" name="password" class="input-field" placeholder="Enter your password" >
              @error('password')
        <span style="color:red">{{ $message }}</span>
    @enderror
          </div>
        </div>
 @if(session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif
        <div class="form-options-row">
          <a href="#" class="forgot-link" onclick="alert('Password reset link sent to registered email.'); return false;">Forgot password?</a>
        </div>

        <button type="submit" class="btn-submit">
          Sign In to Shopkart
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>

        <div class="switch-auth-link">
          Don't have an account? <a href="/register">Register free here</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    function handleLoginSubmit(e) {
      e.preventDefault();
      // TODO: Connect to backend API: POST /api/auth/login with credentials
      window.location.href = '/index';
    }
  </script>
</body>
</html>
