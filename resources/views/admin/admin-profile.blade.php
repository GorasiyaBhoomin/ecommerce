<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Profile</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
  <link href="{{ asset('assets/css/adminprofile.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/admin-notifications.css') }}" rel="stylesheet">
</head>
<body>
<div style="display:flex;flex-wrap:wrap;min-height:1100px;background:#F4F6F5;color:#0F172A;font-family:'Plus Jakarta Sans',system-ui,sans-serif">
      @include('admin.partials.admin-sidebar', ['active' => 'profile', 'showUserPill' => true])
  <main style="flex:999 1 560px;min-width:0;padding:32px;box-sizing:border-box;display:flex;flex-direction:column;gap:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
      <h1 style="margin:0;font-size:28px;font-weight:800">{{ __('Profile') }}</h1>
      @include('admin.partials.notification-bar')
    </div>

    @if (session('success'))
      <div style="padding: 12px 20px; background: #DCFCE7; color: #166534; border-radius: 10px; font-weight: 600; font-size: 14px; border: 1px solid #BBF7D0;">
        {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div style="padding: 12px 20px; background: #FEE2E2; color: #991B1B; border-radius: 10px; font-weight: 600; font-size: 14px; border: 1px solid #FECACA;">
        <ul style="margin: 0; padding-left: 20px;">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div style="display:flex;flex-wrap:wrap;gap:24px;align-items:flex-start">

      <aside style="flex:1 1 280px;min-width:0;display:flex;flex-direction:column;gap:24px">
        <section style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:28px 24px;display:flex;flex-direction:column;align-items:center;gap:10px;text-align:center">
          <div style="width:96px;height:96px;border-radius:999px;background:#0F766E;color:#FFFFFF;font-size:40px;font-weight:800;display:flex;align-items:center;justify-content:center">
            {{ strtoupper(substr($admin->name, 0, 1)) }}
          </div>
          <div style="font-size:20px;font-weight:800;margin-top:6px">{{ $admin->name }}</div>
          <div style="font-size:14px;color:#566579">Store owner</div>
          <div style="font-size:14px;color:#334155">{{ $admin->email }}</div>
          <button style="margin-top:8px;padding:10px 18px;border-radius:10px;border:1px solid #D5DEDB;background:#FFFFFF;color:#0F172A;font:inherit;font-size:13px;font-weight:600">Change photo</button>
        </section>
       
<form method="POST" action="/logout" style="margin:0">
  @csrf
  <button type="submit" style="display:block;width:100%;text-align:center;padding:13px;border-radius:10px;border:1px solid #FCA5A5;background:#FFFFFF;color:#B91C1C;font:inherit;font-size:14px;font-weight:700;cursor:pointer">Sign out</button>
</form>      </aside>

      <div style="flex:2 1 520px;min-width:0;display:flex;flex-direction:column;gap:24px">
        <form method="POST" action="{{ route('admin.profile.update') }}">
          @csrf
          <section style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:24px;display:flex;flex-direction:column;gap:18px;margin-bottom:24px">
            <h2 style="margin:0;font-size:18px;font-weight:700">Personal information</h2>
            <div style="display:flex;flex-wrap:wrap;gap:16px">
              <div style="flex:1 1 220px"><label class="lb" for="p-name">Full name</label><input class="in" id="p-name" name="name" value="{{ old('name', $admin->name) }}"></div>
              <div style="flex:1 1 220px"><label class="lb" for="p-role">Role</label><input class="in" id="p-role" value="Store owner" disabled style="background:#F1F5F4;color:#566579"></div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:16px">
              <div style="flex:1 1 220px"><label class="lb" for="p-mail">Email</label><input class="in" id="p-mail" name="email" value="{{ old('email', $admin->email) }}"></div>
              <div style="flex:1 1 220px"><label class="lb" for="p-mob">Mobile number</label><input class="in" id="p-mob" name="phone" value="{{ old('phone', $admin->phone) }}" placeholder="10 digit number"></div>
            </div>
          </section>

          <section style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:24px;display:flex;flex-direction:column;gap:18px;margin-bottom:24px">
            <h2 style="margin:0;font-size:18px;font-weight:700">Store details</h2>
            <div style="display:flex;flex-wrap:wrap;gap:16px">
              <div style="flex:1 1 220px"><label class="lb" for="s-name">Store name</label><input class="in" id="s-name" value="Shopkart" disabled style="background:#F1F5F4;color:#566579"></div>
              <div style="flex:1 1 220px"><label class="lb" for="s-del">Delivery charge (₹)</label><input class="in" id="s-del" value="49" disabled style="background:#F1F5F4;color:#566579"></div>
            </div>
            <div><label class="lb" for="s-addr">Store address</label><input class="in" id="s-addr" placeholder="[YOUR STORE ADDRESS]" disabled style="background:#F1F5F4;color:#566579"></div>
            <div style="display:flex;flex-wrap:wrap;gap:16px">
              <div style="flex:1 1 220px"><label class="lb" for="s-gst">GST number</label><input class="in" id="s-gst" placeholder="[YOUR GST NUMBER]" disabled style="background:#F1F5F4;color:#566579"></div>
              <div style="flex:1 1 220px"><label class="lb" for="s-sup">Support phone</label><input class="in" id="s-sup" placeholder="[YOUR SUPPORT PHONE]" disabled style="background:#F1F5F4;color:#566579"></div>
            </div>
          </section>

          <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:24px">
            <button type="submit" style="padding:14px 28px;border-radius:12px;border:0;background:#0F766E;color:#FFFFFF;font:inherit;font-size:15px;font-weight:800;cursor:pointer">Save changes</button>
            <a href="/adminorders" style="padding:13px 24px;border-radius:12px;border:1px solid #D5DEDB;background:#FFFFFF;color:#0F172A;font-size:15px;font-weight:700;text-decoration:none">Cancel</a>
          </div>
        </form>

        <form method="POST" action="{{ route('admin.password.update') }}">
          @csrf
          <section style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:24px;display:flex;flex-direction:column;gap:18px;margin-bottom:24px">
            <h2 style="margin:0;font-size:18px;font-weight:700">Change password</h2>
            <div><label class="lb" for="c-old">Current password</label><input class="in" id="c-old" name="current_password" type="password" placeholder="Enter current password"></div>
            <div style="display:flex;flex-wrap:wrap;gap:16px">
              <div style="flex:1 1 220px"><label class="lb" for="c-new">New password</label><input class="in" id="c-new" name="password" type="password" placeholder="New password"></div>
              <div style="flex:1 1 220px"><label class="lb" for="c-new2">Confirm new password</label><input class="in" id="c-new2" name="password_confirmation" type="password" placeholder="Repeat new password"></div>
            </div>
            <div style="margin-top:10px">
              <button type="submit" style="padding:12px 24px;border-radius:10px;border:0;background:#0F172A;color:#FFFFFF;font:inherit;font-size:14px;font-weight:700;cursor:pointer">Update password</button>
            </div>
          </section>
        </form>
      </div>
    </div>
  </main>
</div>
</body>
</html>
