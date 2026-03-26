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
        .status-banner { text-align: center; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 700; font-size: 20px; }
        .status-approved { background-color: #dcfce7; color: #15803d; }
        .status-rejected { background-color: #fee2e2; color: #b91c1c; }
        .status-recommended { background-color: #dbeafe; color: #1d4ed8; }
        .status-pending { background-color: #fef9c3; color: #854d0e; }
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
            <h1>Leave Status Updated</h1>
        </div>
        <div class="content">
            <p class="greeting">Hello {{ $recipientName }},</p>
            <p>Your leave request status has been updated by <strong>{{ $actorName }}</strong>.</p>
            
            @php
                $bannerClass = 'status-pending';
                if(str_contains(strtolower($statusLabel), 'approved')) $bannerClass = 'status-approved';
                elseif(str_contains(strtolower($statusLabel), 'rejected') || str_contains(strtolower($statusLabel), 'not')) $bannerClass = 'status-rejected';
                elseif(str_contains(strtolower($statusLabel), 'recommended')) $bannerClass = 'status-recommended';
            @endphp

            <div class="status-banner {{ $bannerClass }}">
                {{ $statusLabel }}
            </div>

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

            <p>You can view your updated leave dashboard.</p>
            
            <!-- <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="btn">View My Leaves</a>
            </div> -->
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Enaara Management. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
