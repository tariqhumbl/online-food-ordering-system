<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .invoice-container {
            max-width: 650px;
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: auto;
            border-top: 4px solid #28a745;
        }

        .header {
            text-align: start;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 120px;
        }

        .header h2 {
            color: #333;
            margin: 5px 0;
        }

        .invoice-details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .invoice-details th,
        .invoice-details td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .invoice-details th {
            background-color: #f9f9f9;
        }

        .invoice-details td {
            background-color: #f9f9f9;
        }

        .total {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }

        .order-info {
            margin-top: 20px;
            padding: 15px;
            background: #f1f1f1;
            border-left: 5px solid #28a745;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <div class="invoice-container">
        <div class="header">
            <h2>Invoice</h2>
            <p>
                Hello
                <strong>{{ optional($invoice->orderItem->order->customer_information)->name ?? 'Customer' }}</strong>,
                here is your invoice.
            </p>
        </div>

        <div class="order-info">
            <p><strong>Tailor Name:</strong> {{ optional($invoice->orderItem->order->tailor)->name ?? 'N/A' }} </p>
            <p><strong>Order ID:</strong> #{{ optional($invoice->orderItem->order)->id ?? 'N/A' }}</p>
            <p><strong>Delivery Date:</strong> {{ optional($invoice->orderItem->order)->delivery_date ?? 'Not Set' }}
            </p>
            <p><strong>Status:</strong>
                <span
                    class="status-badge">{{ ucfirst(optional($invoice->orderItem->order)->status ?? 'Unknown') }}</span>
            </p>
        </div>

        <table class="invoice-details">
            <tr>
                <th>Item Type</th>
                <td>{{ $invoice->item_type ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Quantity</th>
                <td>{{ $invoice->quantity ?? '0' }}</td>
            </tr>
            <tr>
                <th>Price</th>
                <td>PKR {{ number_format($invoice->price ?? 0, 2) }}</td>
            </tr>
            <tr>
                <th>Tax</th>
                <td>PKR {{ number_format($invoice->tax ?? 0, 2) }}</td>
            </tr>
            <tr>
                <th class="total">Total</th>
                <td class="total">PKR {{ number_format($invoice->total ?? 0, 2) }}</td>
            </tr>
        </table>

        <p class="footer">
            Thank you for your order! If you have any questions, feel free to contact us.
        </p>
    </div>

</body>

</html>
