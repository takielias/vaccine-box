<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Appointment Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #0056b3;
        }

        .important {
            font-weight: bold;
            color: #0056b3;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
<h1>Vaccination Appointment Reminder</h1>
<p>Dear {{ $vaccination->user->name }},</p>
<p>This is a reminder of your upcoming vaccination appointment:</p>
<p><span class="important">Date:</span> {{ $vaccination->vaccination_date->format('l, F j, Y') }}</p>
<p><span class="important">Time:</span> {{ $timeSlot }}</p>
<p><span class="important">Location:</span> {{ $vaccination->vaccinationCenter->name }}</p>
<p>Please note:</p>
<ul>
    <li>Arrive 5 minutes before your scheduled time.</li>
    <li>Bring a valid form of identification.</li>
    <li>Wear a mask and maintain social distancing.</li>
    <li>If you're feeling unwell, please contact us to reschedule.</li>
</ul>
<p>If you need to reschedule or have any questions, please contact us as soon as possible
    at {{env('MAIL_FROM_ADDRESS', 'contact@ebuz.xyz')}}</p>
<p>Thank you for your participation in our vaccination program. Your health and safety are our top priorities.</p>
<div class="footer">
    <p>This is an automated message. Please do not reply to this email.</p>
</div>
</body>
</html>
