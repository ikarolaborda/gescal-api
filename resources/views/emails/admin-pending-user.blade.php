<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Pending Approval</title>
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
            background-color: #3498db;
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
        .user-details {
            background-color: white;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .user-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-details td {
            padding: 8px 0;
        }
        .user-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .button-approve {
            background-color: #27ae60;
            color: white;
        }
        .button-reject {
            background-color: #e74c3c;
            color: white;
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
        <h1>🔔 GESCAL</h1>
        <p>New User Registration</p>
    </div>
    
    <div class="content">
        <h2>Hello, {{ $adminName }}!</h2>
        
        <p>A new user has registered for <strong>{{ $organizationName }}</strong> and requires your approval before they can access the system.</p>
        
        <div class="user-details">
            <h3>Registration Details</h3>
            <table>
                <tr>
                    <td>Name:</td>
                    <td>{{ $pendingUserName }}</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>{{ $pendingUserEmail }}</td>
                </tr>
                <tr>
                    <td>Organization:</td>
                    <td>{{ $organizationName }}</td>
                </tr>
                <tr>
                    <td>Registered At:</td>
                    <td>{{ $registrationDate }}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td><strong>Pending Approval</strong></td>
                </tr>
            </table>
        </div>
        
        <h3>Action Required</h3>
        <p>Please review this registration request and take one of the following actions:</p>
        
        <div class="button-container">
            <a href="{{ $approveUrl }}" class="button button-approve">✓ Approve User</a>
            <a href="{{ $rejectUrl }}" class="button button-reject">✗ Reject User</a>
        </div>
        
        <h3>Next Steps</h3>
        <ul>
            <li><strong>Approve:</strong> The user will receive an email notification and gain immediate access to GESCAL</li>
            <li><strong>Reject:</strong> The user will be notified that their registration was not approved</li>
        </ul>
        
        <p style="margin-top: 30px; font-size: 14px;">
            <strong>Need Help?</strong><br>
            If you have questions about this registration or need assistance, contact support at <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
        </p>
        
        <p>
            Best regards,<br>
            <strong>The GESCAL Team</strong>
        </p>
    </div>
    
    <div class="footer">
        <p>You received this email because you are an administrator for {{ $organizationName }}.</p>
        <p>&copy; {{ date('Y') }} GESCAL - Social Assistance Management System</p>
    </div>
</body>
</html>

