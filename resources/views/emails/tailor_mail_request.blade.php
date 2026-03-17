<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Account Credentials</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);">
        <tr>
            <td style="text-align: center; padding-bottom: 20px;">
                <h1 style="color: #333;">Your Account Credentials</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 20px; font-size: 16px; color: #555;">
                Dear <strong>{{ $name }}</strong>,
                <p>Welcome to <strong>{{ env('APP_NAME') }}</strong>! Your tailor account has been successfully created.</p>

                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>

                <p style="color: #d9534f;"><strong>Important:</strong> Please log in and change your password for security.</p>

                <p>If you have any questions, feel free to contact us.</p>

                <p>Best regards,</p>
                <p><strong>{{ env('APP_NAME') }}</strong></p>
                <p><a href="{{ env('APP_URL') }}" style="color: #007bff; text-decoration: none;">Visit Website</a></p>
            </td>
        </tr>
    </table>
</body>
</html>
