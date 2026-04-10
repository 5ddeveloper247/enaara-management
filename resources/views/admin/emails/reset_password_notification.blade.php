<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password Notification – Enaara HRMS</title>
  <style>
    body { margin: 0; padding: 0; background-color: #f0f2f5; font-family: Arial, sans-serif; }
    .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 10px; overflow: hidden; border: 1px solid #dde3ec; }
    .header { background-color: #1B3A5C; padding: 32px 40px; text-align: center; }
    .header .brand { font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: 0.5px; }
    .header .brand span { color: #D4A843; }
    .header .tagline { font-size: 12px; color: #a0b8cc; margin-top: 6px; }
    .body { padding: 36px 40px; }
    .body h2 { font-size: 20px; color: #1B3A5C; font-weight: 700; margin: 0 0 14px; }
    .body p { font-size: 14px; color: #444444; line-height: 1.75; margin: 0 0 14px; }
    .btn-wrap { text-align: center; margin: 26px 0; }
    .btn { display: inline-block; background-color: #D4A843; color: #1B3A5C; padding: 13px 36px; border-radius: 6px; font-size: 15px; font-weight: 700; text-decoration: none; }
    .highlight { background: #f4f7fb; border-left: 3px solid #1B3A5C; padding: 14px 18px; border-radius: 0 6px 6px 0; margin: 20px 0; font-size: 14px; color: #333; line-height: 1.7; }
    .highlight strong { display: block; color: #1B3A5C; font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px; }
    .divider { border: none; border-top: 1px solid #eeeeee; margin: 24px 0; }
    .note { font-size: 12px; color: #999999; line-height: 1.6; }
    .note a { color: #1B3A5C; }
    .footer { background: #f7f8fa; border-top: 1px solid #e8e8e8; padding: 20px 40px; text-align: center; }
    .footer .footer-brand { font-size: 13px; font-weight: 700; color: #1B3A5C; margin-bottom: 6px; }
    .footer p { font-size: 12px; color: #aaaaaa; line-height: 1.6; margin: 0; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <div class="brand">Enaara <span>HRMS</span></div>
      <div class="tagline">Human Resource Management System</div>
    </div>
    <div class="body">
      <h2>Reset Password Notification</h2>
      <p>Hi <strong>{{ $name }}</strong>,</p>
      <p>You are receiving this email because we received a password reset request for your account.</p>
      
      <div class="btn-wrap">
        <a href="{{ $actionUrl }}" class="btn">Reset Password</a>
      </div>

      <div class="highlight">
        <strong>Security Note</strong>
        This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes. If you did not request a password reset, no further action is required.
      </div>
      
      <hr class="divider" />
      <p class="note">
        If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br/>
        <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
      </p>
    </div>
    <div class="footer">
      <div class="footer-brand">Enaara HRMS</div>
      <p>This is an automated email. Please do not reply directly to this message.<br/>© {{ date('Y') }} Enaara HRMS. All rights reserved.</p>
    </div>
  </div>
</body>
</html>
