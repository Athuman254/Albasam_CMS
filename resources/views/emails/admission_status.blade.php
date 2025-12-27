<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Update</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #25615a;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #25615a;
            margin: 0;
            font-size: 24px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-reviewed {
            background-color: #cce5ff;
            color: #004085;
        }

        .details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .details p {
            margin: 5px 0;
        }

        .footer {
            text-align: center;
            color: #777;
            font-size: 12px;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Admission Portal</h1>
        </div>

        <p>Dear {{ $name }},</p>

        <p>This is to inform you that there has been an update regarding your admission application (<strong>#{{ $appNumber }}</strong>).</p>

        <div class="text-center">
            <span class="status-badge status-{{ $status }}">
                Current Status: {{ strtoupper($status) }}
            </span>
        </div>

        @if($status === 'approved')
        <p><strong>Congratulations!</strong> Your application has been approved. Welcome to our school community! Our admissions office will contact you soon with further instructions regarding enrollment and documentation.</p>
        @elseif($status === 'rejected')
        <p>We regret to inform you that your application has not been successful at this time.</p>
        @else
        <p>Your application has been reviewed by our admissions team. Please find our feedback below.</p>
        @endif

        @if($notes)
        <div class="details">
            <strong>Admin Feedback:</strong>
            <p>{{ $notes }}</p>
        </div>
        @endif

        <p>You can track your application status at any time on our website using your application number and guardian's email.</p>

        <div class="footer">
            <p>This is an automated message. Please do not reply directly to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>