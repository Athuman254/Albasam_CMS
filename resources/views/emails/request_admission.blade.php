<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #0056b3;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 15px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            background: #f1f1f1;
            margin: 5px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Admission Request</h2>

        <p>Dear Admin,</p>

        <p>A new admission request has been received with the following details:</p>

        <ul>
            <li><strong>Email:</strong> {{ $userEmail }}</li>
            <li><strong>Date:</strong> {{ $requestDate }}</li>
        </ul>

        <p>Please process this request at your earliest convenience.</p>

        <p>Best regards,<br>
        Your Website</p>

        <div class="footer">
            This is an automated message. Please do not reply.
        </div>
    </div>
</body>
</html>
