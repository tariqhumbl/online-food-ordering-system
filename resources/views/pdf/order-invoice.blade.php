<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #2d3748;
            line-height: 1.5;
            background: #fff;
        }
        .invoice {
            max-width: 720px;
            margin: 0 auto;
            padding: 0 36px 36px;
        }
        /* Top accent bar */
        .accent-bar {
            height: 6px;
            background: #2563eb;
            margin: 0 -36px 28px -36px;
        }
        .header {
            width: 100%;
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        .header td { vertical-align: top; padding: 0 8px 0 0; }
        .header td:last-child { text-align: right; padding: 0 0 0 8px; }
        .brand {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }
        .brand-accent { color: #2563eb; }
        .tagline {
            font-size: 10px;
            color: #64748b;
            letter-spacing: 0.3px;
        }
        .inv-label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 6px;
        }
        .inv-number {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.3px;
        }
        .inv-date {
            font-size: 10px;
            color: #64748b;
            margin-top: 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            margin-top: 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .section-block {
            margin-bottom: 26px;
        }
        .section-title {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { vertical-align: top; padding: 0 16px 0 0; width: 50%; }
        .info-grid td:last-child { padding: 0 0 0 16px; }
        .bill-to .name {
            font-weight: 700;
            font-size: 14px;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .bill-to .line { color: #475569; font-size: 11px; margin-bottom: 3px; }
        .bill-to .line:last-of-type { margin-bottom: 0; }
        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 11px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .items-table thead th {
            text-align: left;
            padding: 14px 16px;
            background: #1e293b;
            color: #f8fafc;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .items-table thead th.col-qty { text-align: center; }
        .items-table thead th.col-price,
        .items-table thead th.col-total { text-align: right; }
        .items-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .items-table tbody tr:nth-child(even) { background: #f8fafc; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table .col-item { width: 44%; font-weight: 500; }
        .items-table .col-qty { width: 14%; text-align: center; color: #64748b; }
        .items-table .col-price { width: 20%; text-align: right; color: #475569; }
        .items-table .col-total { width: 22%; text-align: right; font-weight: 600; color: #1e293b; }
        /* Totals panel */
        .totals-panel {
            margin-top: 24px;
            margin-left: auto;
            width: 300px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-collapse: collapse;
        }
        .totals-panel td { padding: 12px 20px; border-bottom: 1px solid #e2e8f0; }
        .totals-panel tr:last-child td { border-bottom: none; }
        .totals-panel .label { color: #64748b; font-size: 11px; }
        .totals-panel .value { text-align: right; font-weight: 600; color: #334155; }
        .totals-panel .grand-row td {
            background: #1e293b;
            color: #f8fafc;
            padding: 16px 20px;
            font-size: 12px;
        }
        .totals-panel .grand-row .label { color: #cbd5e1; font-weight: 700; }
        .totals-panel .grand-row .value { color: #fff; font-size: 16px; font-weight: 700; }
        .notes-box {
            background: #f1f5f9;
            border-left: 4px solid #2563eb;
            padding: 14px 18px;
            margin-top: 24px;
            font-size: 10px;
            color: #475569;
        }
        .notes-box strong { color: #334155; margin-right: 6px; }
        .footer {
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .footer strong { color: #64748b; }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="accent-bar"></div>

        <table class="header" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td>
                    <div class="brand">{{ config('app.name') }} <span class="brand-accent">Invoice</span></div>
                    <div class="tagline">Food order · Delivery receipt</div>
                </td>
                <td>
                    <div class="inv-label">Invoice number</div>
                    <div class="inv-number">{{ $order->order_number ?? '—' }}</div>
                    <div class="inv-date">{{ $order->created_at ? $order->created_at->format('F j, Y \a\t g:i A') : '—' }}</div>
                    @if(isset($order->status))
                    <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                    @endif
                </td>
            </tr>
        </table>

        <table class="info-grid section-block" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="section-title">Bill to</div>
                    <div class="bill-to">
                        <div class="name">{{ $order->user->name ?? 'Customer' }}</div>
                        <div class="line">{{ $order->user->email ?? '' }}</div>
                        @if(!empty($order->customer_phone))
                        <div class="line">Phone: {{ $order->customer_phone }}</div>
                        @endif
                        <div class="line" style="margin-top:8px;">{{ $order->delivery_address ?? '—' }}</div>
                    </div>
                </td>
                <td>
                    <div class="section-title">Restaurant</div>
                    <div class="bill-to">
                        <div class="name">{{ $order->restaurant->name ?? '—' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-block">
            <div class="section-title">Order items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-item">Item</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-price">Unit price</th>
                        <th class="col-total">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items ?? [] as $item)
                    <tr>
                        <td class="col-item">{{ $item->item_name ?? '—' }}</td>
                        <td class="col-qty">{{ $item->quantity ?? 0 }}</td>
                        <td class="col-price">{{ number_format((float)($item->unit_price ?? 0), 2) }} PKR</td>
                        <td class="col-total">{{ number_format((float)($item->subtotal ?? 0), 2) }} PKR</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals-panel" cellpadding="0" cellspacing="0">
                @if((float)($order->subtotal ?? 0) > 0)
                <tr><td class="label">Subtotal</td><td class="value">{{ number_format((float)$order->subtotal, 2) }} PKR</td></tr>
                @endif
                @if((float)($order->delivery_fee ?? 0) > 0)
                <tr><td class="label">Delivery fee</td><td class="value">{{ number_format((float)$order->delivery_fee, 2) }} PKR</td></tr>
                @endif
                @if((float)($order->tax ?? 0) > 0)
                <tr><td class="label">Tax</td><td class="value">{{ number_format((float)$order->tax, 2) }} PKR</td></tr>
                @endif
                <tr class="grand-row"><td class="label">Total</td><td class="value">{{ number_format((float)($order->total ?? 0), 2) }} PKR</td></tr>
            </table>
        </div>

        @if(!empty($order->notes))
        <div class="notes-box"><strong>Order notes:</strong> {{ $order->notes }}</div>
        @endif

        <div class="footer">
            Thank you for ordering with us. This is a computer-generated invoice and does not require a signature.<br>
            <strong>{{ config('app.name') }}</strong> — {{ config('app.url') ?? 'Food delivery' }}
        </div>
    </div>
</body>
</html>
