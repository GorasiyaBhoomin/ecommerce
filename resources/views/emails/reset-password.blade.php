<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Reset Your Password | Shopkart</title>
<style>
  body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
  table, td { mso-table-lspace:0; mso-table-rspace:0; border-collapse:collapse; }
  body { margin:0 !important; padding:0 !important; width:100% !important; background:#eef1f6; font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#172338; }
  .wrapper { padding:36px 12px; }
  .container { width:600px; max-width:600px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(23,35,56,.08); }
  .header { background:#0F766E; background-image:linear-gradient(135deg,#0F766E 0%,#0D9488 100%); padding:28px 36px; text-align:center; }
  .logo { color:#ffffff; font-size:26px; font-weight:800; letter-spacing:1px; text-decoration:none; }
  .logo span { color:#5EEAD4; }
  .content { padding:36px 36px 28px; }
  .title { font-size:22px; font-weight:800; color:#0F172A; margin:0 0 12px; }
  .text { font-size:14px; line-height:1.7; color:#475569; margin:0 0 20px; }
  .btn-wrap { text-align:center; margin:30px 0; }
  .btn-reset { display:inline-block; background:#0D9488; color:#ffffff !important; font-size:15px; font-weight:700; text-decoration:none; padding:14px 32px; border-radius:8px; box-shadow:0 4px 14px rgba(13,148,136,.35); }
  .btn-reset:hover { background:#0F766E; }
  .notice-box { background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:16px; margin:24px 0; font-size:12px; line-height:1.6; color:#64748B; }
  .footer { background:#F1F5F9; padding:20px 36px; text-align:center; font-size:12px; color:#94A3B8; }
</style>
</head>
<body style="margin:0; padding:0; background:#eef1f6;">
<table role="presentation" width="100%" class="wrapper" cellspacing="0" cellpadding="0" border="0" align="center">
  <tr>
    <td align="center">
      <table role="presentation" class="container" cellspacing="0" cellpadding="0" border="0">
        <!-- Header -->
        <tr>
          <td class="header">
            <span class="logo">SHOP<span>KART</span></span>
          </td>
        </tr>

        <!-- Content -->
        <tr>
          <td class="content">
            <h1 class="title">Reset Your Password</h1>
            <p class="text">
              Hello {{ $user->name ?? 'Valued Customer' }},
            </p>
            <p class="text">
              You are receiving this email because we received a password reset request for your Shopkart account. Click the button below to choose a new password:
            </p>

            <div class="btn-wrap">
              <a href="{{ $resetUrl }}" class="btn-reset" target="_blank">Reset Password</a>
            </div>

            <div class="notice-box">
              <strong>Notice:</strong> This password reset link will expire in <strong>60 minutes</strong>.<br>
              If you did not request a password reset, no further action is required and your account remains secure.
            </div>

            <p style="font-size:12px; color:#94A3B8; line-height:1.6; margin:20px 0 0; word-break:break-all;">
              If you are having trouble clicking the "Reset Password" button, copy and paste this URL into your web browser:<br>
              <a href="{{ $resetUrl }}" style="color:#0D9488;">{{ $resetUrl }}</a>
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td class="footer">
            &copy; {{ date('Y') }} Shopkart Ecommerce. All rights reserved.
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

