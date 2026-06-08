<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Restaurant Approved</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);">
        <tr>
            <td style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <h1 style="color: #333; margin: 0;">Restaurant Approved</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px 0; font-size: 16px; color: #555; line-height: 1.6;">
                <p>Dear <strong>{{ $name }}</strong>,</p>
                <p>Great news! Your restaurant <strong>{{ $restaurantName }}</strong> has been approved on {{ config('app.name') }}.</p>
                <p>You can now log in with the email and password you registered with and start managing your menu and orders.</p>
                <p style="margin-top: 20px;">
                    <a href="{{ $loginUrl }}" style="display: inline-block; background: #0d6efd; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Log in to your dashboard</a>
                </p>
                <p>Best regards,<br><strong>{{ config('app.name') }}</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
