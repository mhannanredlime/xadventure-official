<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Contact Form Submission - {{ $subject }}</title>
    <style>
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: auto;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .header {
            background: #059669;
            color: white;
            padding: 16px;
            text-align: center;
            font-size: 20px;
            border-radius: 6px 6px 0 0;
        }

        .content {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 6px 6px;
        }

        .contact-details h3 {
            margin-bottom: 10px;
        }

        .detail-row {
            margin-bottom: 6px;
            display: flex;
        }

        .detail-label {
            width: 80px;
            font-weight: bold;
        }

        .message-box {
            background: #ffffff;
            padding: 10px;
            border-left: 4px solid #059669;
            margin-top: 15px;
            white-space: pre-wrap;
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #555;
        }
    </style>
</head>

<body>

<div class="email-container">
    <div class="header">
        New Contact Form Submission
    </div>

    <div class="content">

        <p>You’ve received a new contact form message. Details are below:</p>

        <div class="contact-details">
            <h3>👤 Contact Information</h3>

            {{-- <div class="detail-row">
                <div class="detail-label">Name:</div>
                <div class="detail-value">{{ $name }}</div>
            </div> --}}

            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value">{{ $email }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Subject:</div>
                <div class="detail-value">{{ $subject }}</div>
            </div>
        </div>

        <h3>📩 Message</h3>
        <div class="message-box">
            {{ $user_message }}
        </div>

        <div class="footer">
            <p><strong>Received At:</strong> {{ $received_at }}</p>
            <p>This is an automated email from your website’s contact form.</p>
        </div>
    </div>
</div>

</body>
</html>
