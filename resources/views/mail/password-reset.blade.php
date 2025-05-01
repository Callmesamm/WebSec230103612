<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    <p>Hello {{ $notifiable->name }},</p>
    <p>We received a request to reset your password. Click the button below to reset it:</p>
    <p><a href="{{ $actionUrl }}" style="padding: 10px; background-color: #007bff; color: white; text-decoration: none;">Reset Password</a></p>
    <p>If you did not request a password reset, please ignore this email.</p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>