<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Approved</title>
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
            background-color: #27ae60;
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
        .success-box {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 15px 0;
        }
        .roles-list {
            background-color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .roles-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .roles-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .roles-list li:last-child {
            border-bottom: none;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
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
        <h1>✓ GESCAL</h1>
        <p>Account Approved!</p>
    </div>
    
    <div class="content">
        <h2>Congratulations, {{ $userName }}!</h2>
        
        <div class="success-box">
            <p><strong>Your account has been approved!</strong></p>
            <p>You now have full access to GESCAL for <strong>{{ $organizationName }}</strong>.</p>
        </div>
        
        <h3>Your Assigned Roles</h3>
        <p>You have been assigned the following role(s):</p>
        
        <div class="roles-list">
            <ul>
                @foreach($roles as $role)
                <li>{{ $role }}</li>
                @endforeach
            </ul>
        </div>
        
        <h3>Get Started</h3>
        <p>You can now log in to GESCAL and start using the system:</p>
        
        <center>
            <a href="{{ $loginUrl }}" class="button">Login to GESCAL</a>
        </center>
        
        <h3>What You Can Do</h3>
        <p>Depending on your assigned roles, you may be able to:</p>
        <ul>
            <li><strong>Social Worker:</strong> Manage cases, submit approval requests, and work with families</li>
            <li><strong>Coordinator:</strong> Review and approve cases, manage workflows</li>
            <li><strong>Organization Admin:</strong> Manage users, configure settings, approve registrations</li>
            <li><strong>Organization Super Admin:</strong> Full system access and administrative capabilities</li>
        </ul>
        
        <p style="margin-top: 30px; font-size: 14px;">
            <strong>Need Help?</strong><br>
            If you have any questions or need assistance getting started, please contact our support team at <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
        </p>
        
        <p>
            Welcome to GESCAL!<br>
            <strong>The GESCAL Team</strong>
        </p>
    </div>
    
    <div class="footer">
        <p>You can now access all features available to your assigned roles.</p>
        <p>&copy; {{ date('Y') }} GESCAL - Social Assistance Management System</p>
    </div>
</body>
</html>

