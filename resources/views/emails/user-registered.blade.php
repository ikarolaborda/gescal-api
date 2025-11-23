<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to GESCAL</title>
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
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #7f8c8d;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid: #ffc107;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to GESCAL</h1>
        <p>Your Social Assistance Management System</p>
    </div>
    
    <div class="content">
        <h2>Hello, {{ $userName }}!</h2>
        
        <p>Congratulations! Your organization <strong>{{ $organizationName }}</strong> has been successfully created in GESCAL.</p>
        
        <div class="highlight">
            <p><strong>You are now the Organization Super Administrator!</strong></p>
            <p>As the first user, you have full administrative privileges to manage your organization, approve new users, and configure the system.</p>
        </div>
        
        <h3>Next Steps:</h3>
        <ol>
            <li>Log in to your account using the button below</li>
            <li>Complete your organization's profile</li>
            <li>Invite team members to join your organization</li>
            <li>Review and approve new user requests</li>
            <li>Start managing your social assistance programs</li>
        </ol>
        
        <center>
            <a href="{{ $loginUrl }}" class="button">Access GESCAL</a>
        </center>
        
        <h3>Your Responsibilities:</h3>
        <ul>
            <li><strong>User Management:</strong> Approve or reject new user registration requests</li>
            <li><strong>Organization Settings:</strong> Configure organization details and preferences</li>
            <li><strong>Access Control:</strong> Manage user roles and permissions</li>
            <li><strong>Data Security:</strong> Ensure compliance with LGPD and data protection policies</li>
        </ul>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team at <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.</p>
        
        <p>Thank you for choosing GESCAL!</p>
        
        <p>
            Best regards,<br>
            <strong>The GESCAL Team</strong>
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} GESCAL - Social Assistance Management System</p>
    </div>
</body>
</html>

