<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body>
    <p>You requested to reset your password. Click the link below to reset it:</p>
    <a href="{{ url('/reset-password?token=' . $token) }}">Reset Password</a>
    <p>If you did not request this, please ignore this email.</p>
</body>
</html>
