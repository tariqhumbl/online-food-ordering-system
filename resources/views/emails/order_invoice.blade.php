<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your order invoice</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);">
        <tr>
            <td style="text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <h1 style="color: #333; margin: 0;">Your order has been delivered</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px 0; font-size: 16px; color: #555; line-height: 1.6;">
                <p>Dear <strong>{{ $order->user->name ?? 'Customer' }}</strong>,</p>
                <p>Thank you for ordering with us. Your order <strong>{{ $order->order_number }}</strong> has been delivered.</p>
                <p>Please find your invoice attached to this email as a PDF. You can save it for your records.</p>
                <p style="margin-top: 20px;">Best regards,<br><strong>{{ config('app.name') }}</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
