<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password | Shopkart</title>
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
          Choose a new password to protect your account.
        </h2>

        <ul class="feature-list">
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Security Tip:</strong> Use at least 8 characters with a mix of letters and numbers.</div>
          </li>
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Immediate Access:</strong> Once updated, sign in right away with your new credentials.</div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Right Form Card -->
    <div class="form-column">
      <div class="auth-panel-head">
        <h1 class="auth-panel-title">Set New Password</h1>
        <p class="auth-panel-sub">Enter your email and create your new secure password below.</p>
      </div>

      @if (session('error'))
        <div class="auth-alert" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 600;">
          {{ session('error') }}
        </div>
      @endif

      <form class="auth-form" action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
          <label class="form-label" for="resetEmail">Email Address</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="20" height="16" x="2" y="4" rx="2"/>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
            <input type="email" name="email" id="resetEmail" class="input-field" placeholder="name@example.com" value="{{ old('email', $email) }}" required>
          </div>
          @error('email')
            <span style="color:#DC2626; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label class="form-label" for="resetPassword">New Password</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" name="password" id="resetPassword" class="input-field" placeholder="At least 8 characters" required autofocus>
          </div>
          @error('password')
            <span style="color:#DC2626; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <label class="form-label" for="resetPasswordConfirmation">Confirm New Password</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" name="password_confirmation" id="resetPasswordConfirmation" class="input-field" placeholder="Repeat your new password" required>
          </div>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 8px;">
          Reset Password &amp; Sign In
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

