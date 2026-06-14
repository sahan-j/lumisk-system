<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #0f172a; background: #f8fafc; margin: 0; padding: 0; }
  .wrapper { max-width: 520px; margin: 32px auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
  .header { background: linear-gradient(135deg, #00d4ff, #6d5cff); padding: 24px 32px; }
  .header-logo { font-size: 20px; font-weight: bold; color: #ffffff; }
  .body { padding: 32px; }
  .message { font-size: 14px; color: #374151; line-height: 1.7; margin-bottom: 24px; }
  .btn { display: inline-block; background: linear-gradient(135deg, #00d4ff, #6d5cff); color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; }
  .expiry { font-size: 12px; color: #94a3b8; margin-top: 20px; }
  .footer { padding: 16px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-logo">Lumisk.</div>
  </div>
  <div class="body">
    <div class="message">
      You requested a password reset for your Lumisk client portal account.<br><br>
      Click the button below to reset your password:
    </div>
    <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
    <div class="expiry">This link expires in 60 minutes. If you didn't request this, ignore this email.</div>
  </div>
  <div class="footer">
    Lumisk Technology &nbsp;·&nbsp; lumisktechnology.com
  </div>
</div>
</body>
</html>
