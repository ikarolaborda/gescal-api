<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Decision</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 15px 0;
        }
        .reason-box {
            background-color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            border: 1px solid #dee2e6;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #7f8c8d;
        }
        .support-box {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GESCAL</h1>
        <p>Registration Decision</p>
    </div>
    
    <div class="content">
        <h2>Hello, {{ $userName }}</h2>
        
        <div class="info-box">
            <p><strong>Registration Decision</strong></p>
            <p>We regret to inform you that your registration request for <strong>{{ $organizationName }}</strong> has not been approved at this time.</p>
        </div>
        
        <h3>Reason for Decision</h3>
        <div class="reason-box">
            <p>{{ $rejectionReason }}</p>
        </div>
        
        <div class="support-box">
            <h3>Need Assistance?</h3>
            <p>If you believe this decision was made in error or if you have questions about this decision, please contact your organization administrator or our support team.</p>
            <p><strong>Support Email:</strong> <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></p>
        </div>
        
        <p style="margin-top: 30px; font-size: 14px;">
            <strong>What This Means:</strong><br>
            You will not be able to access GESCAL for {{ $organizationName }} at this time. If you need to reapply or discuss this decision, please reach out to your organization's administrator directly.
        </p>
        
        <p>
            Thank you for your interest in GESCAL.<br>
            <strong>The GESCAL Team</strong>
        </p>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} GESCAL - Social Assistance Management System</p>
    </div>
</body>
</html>

