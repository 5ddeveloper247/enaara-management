<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #0f172a; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { margin-bottom: 30px; }
        .greeting { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 16px; }
        .details-box { background-color: #f1f5f9; padding: 24px; border-radius: 12px; margin-bottom: 24px; border: 1px solid #e2e8f0; }
        .detail-row { display: flex; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .detail-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .label { font-weight: 600; color: #64748b; width: 120px; }
        .value { color: #0f172a; flex: 1; }
        .footer { text-align: center; font-size: 14px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; transition: background-color 0.2s; }
        .btn:hover { background-color: #1d4ed8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Enaara Management</h1>
        </div>
        <div class="content">
            <p class="greeting">Hello {{ $recipientName }},</p>
            <p><strong>{{ $senderName }}</strong> has submitted a leave request that requires your approval.</p>
            
            <div class="details-box">
                <div class="detail-row">
                    <span class="label">Leave Type:</span>
                    <span class="value">{{ $leaveType }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Start Date:</span>
                    <span class="value">{{ $startDate }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">End Date:</span>
                    <span class="value">{{ $endDate }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Duration:</span>
                    <span class="value">{{ $duration }} day(s)</span>
                </div>
            </div>

            <p>Please review and take action on this request.</p>
            
            <!-- <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="btn">View Request Dashboard</a>
            </div> -->
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Enaara Management. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
