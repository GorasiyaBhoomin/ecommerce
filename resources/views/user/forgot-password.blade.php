<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | Shopkart</title>
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
          Recover your Shopkart account securely and quickly.
        </h2>

        <ul class="feature-list">
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Instant Reset Link:</strong> We'll email you a secure link valid for 60 minutes.</div>
          </li>
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Account Protection:</strong> Your orders, cart, and profile remain completely safe.</div>
          </li>
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Help &amp; Support:</strong> Contact our team anytime if you need login assistance.</div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Right Form Card -->
    <div class="form-column">
      <div class="auth-panel-head">
        <h1 class="auth-panel-title">Forgot Password?</h1>
        <p class="auth-panel-sub">No worries! Enter your registered email address and we'll send you a password reset link.</p>
      </div>

      @if (session('success'))
        <div class="auth-alert auth-alert-success" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 14px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; font-weight: 600;">
          {{ session('success') }}
        </div>
      @endif

      @if (session('warning'))
        <div class="auth-alert" style="background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; padding: 14px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13px;">
          {{ session('warning') }}
          @if (session('reset_link'))
            <div style="margin-top: 10px;">
              <a href="{{ session('reset_link') }}" style="display: inline-block; background: #0D9488; color: #fff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 12px;">Click here to Reset Password Now &rarr;</a>
            </div>
          @endif
        </div>
      @endif

      @if (session('error'))
        <div class="auth-alert" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 600;">
          {{ session('error') }}
        </div>
      @endif

      <form class="auth-form" action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="form-group">
          <label class="form-label" for="resetEmail">Registered Email Address</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="20" height="16" x="2" y="4" rx="2"/>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
            <input type="email" name="email" id="resetEmail" class="input-field" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
          </div>
          @error('email')
            <span style="color:#DC2626; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
          @enderror
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 8px;">
          Send Password Reset Link
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>

        <div class="switch-auth-link" style="margin-top: 24px;">
          Remembered your password? <a href="{{ route('login') }}">Back to Sign In</a>
        </div>
      </form>
    </div>
  </div>

</body>
</html>

