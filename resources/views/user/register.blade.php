<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account | Shopkart</title>
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
          Create your account and start saving today.
        </h2>

        <ul class="feature-list">
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Track Every Order:</strong> Live 5-stage dispatch updates from Received to Delivered.</div>
          </li>
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Saved Addresses:</strong> Checkout in seconds with your saved delivery details.</div>
          </li>
          <li class="feature-item">
            <div class="feature-check-icon">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            <div><strong>Member-Only Offers:</strong> Early access to festive deals on electronics and farm groceries.</div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Right Form Card -->
    <div class="form-column">
      <div class="auth-panel-head">
        <h1 class="auth-panel-title">Create your account</h1>
        <p class="auth-panel-sub">It takes less than a minute. No charges, no commitment.</p>
      </div>

      @if ($errors->any())
        <div class="auth-alert auth-alert-error">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form class="auth-form" action="{{ url('/register') }}" method="post">
        @csrf
        <div class="form-group">
          <label class="form-label" for="regName">Full Name *</label>
          <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="regName" name="name" class="input-field" value="{{ old('name') }}" placeholder="Enter Your Name" >
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label" for="regMobile">Mobile (+91) *</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/>
                <path d="M12 18h.01"/>
              </svg>
              <input type="tel" id="regMobile" name="phone" class="input-field" value="{{ old('phone') }}" placeholder="Enter 10 Digits Mobile Number" >
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="regEmail">Email Address *</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="20" height="16" x="2" y="4" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
              <input type="email" id="regEmail" name="email" class="input-field" value="{{ old('email') }}" placeholder="email address" >
            </div>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label" for="regPass">Create Password *</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="18" height="11" x="3" y="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <input type="password" id="regPass" name="password" class="input-field" placeholder="Min. 8 characters" >
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="regConfirmPass">Confirm Password *</label>
            <div class="input-wrap">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="18" height="11" x="3" y="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <input type="password" id="regConfirmPass" name="password_confirmation" class="input-field" placeholder="Confirm password" >
            </div>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          Create Account &amp; Continue
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>

        <div class="switch-auth-link">
          Already registered? <a href="/login">Sign in here</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
