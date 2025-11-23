<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Report Disabled</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 20px; border-radius: 4px;">
        <h1 style="color: #856404; margin-top: 0;">⚠️ Scheduled Report Disabled</h1>

        <p>Hello {{ $schedule->user->name }},</p>

        <p>Your scheduled report "<strong>{{ $schedule->name }}</strong>" has been automatically disabled due to repeated execution failures.</p>

        <div style="background-color: #fff; border: 1px solid #dee2e6; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #333;">Schedule Details</h3>
            <ul style="list-style: none; padding: 0;">
                <li><strong>Schedule ID:</strong> {{ $schedule->id }}</li>
                <li><strong>Name:</strong> {{ $schedule->name }}</li>
                <li><strong>Entity Type:</strong> {{ ucfirst($schedule->entity_type) }}</li>
                <li><strong>Format:</strong> {{ strtoupper($schedule->format) }}</li>
                <li><strong>Frequency:</strong> {{ ucfirst($schedule->frequency) }}</li>
                <li><strong>Failure Count:</strong> {{ $schedule->failure_count }}</li>
                <li><strong>Last Attempt:</strong> {{ $schedule->last_execution_at?->format('M d, Y H:i:s') ?? 'Never' }}</li>
            </ul>
        </div>

        <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #721c24;">Action Required</h3>
            <p style="margin-bottom: 10px;">To resume this scheduled report:</p>
            <ol style="margin: 0; padding-left: 20px;">
                <li>Review the schedule configuration</li>
                <li>Check that all parameters are valid</li>
                <li>Verify database access and permissions</li>
                <li>Re-enable the schedule through the API or admin panel</li>
            </ol>
        </div>

        <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
            <strong>Note:</strong> The schedule will remain disabled until manually re-enabled. Check system logs for detailed error messages.
        </p>
    </div>

    <div style="text-align: center; color: #6c757d; font-size: 12px; margin-top: 20px;">
        <p>If you did not create this schedule or have questions, please contact your administrator.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
