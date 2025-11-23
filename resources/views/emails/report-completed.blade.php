<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Report is Ready</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; border-radius: 8px; padding: 30px; margin-bottom: 20px;">
        <h1 style="color: #2c3e50; margin-top: 0;">Your Report is Ready</h1>

        <p>Hello {{ $report->user->name }},</p>

        <p>Your {{ ucfirst($report->entity_type) }} report has been generated successfully and is ready for download.</p>

        <div style="background-color: #fff; border-left: 4px solid #3498db; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #2c3e50;">Report Details</h3>
            <ul style="list-style: none; padding: 0;">
                <li><strong>Report ID:</strong> {{ $report->id }}</li>
                <li><strong>Entity Type:</strong> {{ ucfirst($report->entity_type) }}</li>
                <li><strong>Format:</strong> {{ strtoupper($report->format) }}</li>
                <li><strong>Records:</strong> {{ $report->metadata['record_count'] ?? 'N/A' }}</li>
                <li><strong>Generated:</strong> {{ $report->generated_at?->format('M d, Y H:i:s') }}</li>
            </ul>
        </div>

        @if($report->isDownloadable())
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/api/v1/reports/{{ $report->id }}/download"
                   style="background-color: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                    Download Report
                </a>
            </div>
        @endif

        <p style="color: #7f8c8d; font-size: 14px; margin-top: 30px;">
            <strong>Note:</strong> This report will be available for download for {{ config('reports.file_retention_days', 90) }} days.
        </p>
    </div>

    <div style="text-align: center; color: #7f8c8d; font-size: 12px; margin-top: 20px;">
        <p>If you did not request this report, please contact your administrator.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
