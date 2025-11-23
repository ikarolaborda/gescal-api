<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Pending Approval</title>
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
            background-color: #2c3e50;
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
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GESCAL</h1>
        <p>Registration Received</p>
    </div>
    
    <div class="content">
        <h2>Hello, {{ $userName }}!</h2>
        
        <p>Thank you for registering with GESCAL for <strong>{{ $organizationName }}</strong>.</p>
        
        <div class="info-box">
            <p><strong>Your account is pending approval.</strong></p>
            <p>An administrator from your organization will review your registration request and approve or reject it. You will receive an email notification once a decision has been made.</p>
        </div>
        
        <h3>What Happens Next?</h3>
        <ol>
            <li>An organization administrator will receive a notification about your registration</li>
            <li>They will review your request and make a decision</li>
            <li>You will receive an email with the approval decision</li>
            <li>If approved, you will be able to log in and start using GESCAL</li>
        </ol>
        
        <h3>Change Your Mind?</h3>
        <p>If you wish to cancel your registration request, you can do so using the link below. This link will expire on <strong>{{ $expirationDate }}</strong>.</p>
        
        <center>
            <a href="{{ $cancellationUrl }}" class="button">Cancel My Registration</a>
        </center>
        
        <p style="margin-top: 30px; font-size: 14px;">
            <strong>Need Help?</strong><br>
            If you did not request this registration or have any questions, please contact our support team at <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
        </p>
        
        <p>
            Best regards,<br>
            <strong>The GESCAL Team</strong>
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>The cancellation link will expire on {{ $expirationDate }}.</p>
        <p>&copy; {{ date('Y') }} GESCAL - Social Assistance Management System</p>
    </div>
</body>
</html>

