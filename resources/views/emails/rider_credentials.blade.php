<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Delivery Rider Account</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);">
        <tr>
            <td style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <h1 style="color: #333; margin: 0;">Delivery Rider Account</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px 0; font-size: 16px; color: #555; line-height: 1.6;">
                <p>Dear <strong>{{ $name }}</strong>,</p>
                <p>Your delivery rider account has been created on {{ config('app.name') }}. Use the credentials below to log in to the rider portal and manage your deliveries.</p>
                <table style="width: 100%; background: #f8f9fa; border-radius: 6px; padding: 16px; margin: 16px 0;">
                    <tr><td style="padding: 4px 0;"><strong>Email:</strong></td><td>{{ $email }}</td></tr>
                    <tr><td style="padding: 4px 0;"><strong>Password:</strong></td><td><code style="background: #fff; padding: 2px 8px; border-radius: 4px;">{{ $password }}</code></td></tr>
                </table>
                <p style="margin-top: 20px;">
                    <a href="{{ $loginUrl }}" style="display: inline-block; background: #0d6efd; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Log in to rider portal</a>
                </p>
                <p style="color: #856404; background: #fff3cd; padding: 12px; border-radius: 6px; margin-top: 20px;">
                    <strong>Important:</strong> Please log in and change your password in Settings for security.
                </p>
                <p>Best regards,<br><strong>{{ config('app.name') }}</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
