<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Employee Leave Approved – Enaara HRMS</title>
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
    .highlight { background: #f4f7fb; border-left: 3px solid #1B3A5C; padding: 14px 18px; border-radius: 0 6px 6px 0; margin: 20px 0; }
    .highlight strong { display: block; color: #1B3A5C; font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 10px; }
    .info-row { display: flex; justify-content: space-between; font-size: 13px; padding: 7px 0; border-bottom: 1px solid #eaeaea; color: #444; }
    .info-row:last-child { border-bottom: none; padding-bottom: 0; }
    .info-row .label { color: #888888; min-width: 140px; }
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
      <h2>Employee leave approved</h2>
      <p>Hi <strong>{{ $recipientName }}</strong>,</p>
      <p>This is to inform you that an employee's leave request has been officially approved. They will be away during the period specified below.</p>
      <div class="highlight">
        <strong>Leave Details</strong>
        <div class="info-row"><span class="label">Employee</span><span>{{ $senderName }} — {{ $employeeId }}</span></div>
        <div class="info-row"><span class="label">Department</span><span>{{ $departmentName }}</span></div>
        <div class="info-row"><span class="label">Leave Type</span><span>{{ $leaveType }}</span></div>
        <div class="info-row"><span class="label">From</span><span>{{ $startDate }}</span></div>
        <div class="info-row"><span class="label">To</span><span>{{ $endDate }}</span></div>
        <div class="info-row"><span class="label">Total Days</span><span>{{ (float)$duration }} days</span></div>
        <div class="info-row"><span class="label">Approved By</span><span>{{ $actorName }}</span></div>
      </div>
      <p>You can view the leave calendar in Enaara HRMS for more details.</p>
      <div class="btn-wrap">
        <a href="{{ $actionUrl }}" class="btn">View Details</a>
      </div>
    </div>
    <div class="footer">
      <div class="footer-brand">Enaara HRMS</div>
      <p>This is an automated email. Please do not reply directly to this message.<br/>© {{ date('Y') }} Enaara HRMS. All rights reserved.</p>
    </div>
  </div>
</body>
</html>
