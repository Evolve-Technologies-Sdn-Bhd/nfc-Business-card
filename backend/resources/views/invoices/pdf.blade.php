<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }
        
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        
        .header-right {
            text-align: right;
        }
        
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .invoice-meta {
            font-size: 11px;
            color: #666;
        }
        
        .invoice-meta strong {
            color: #333;
        }
        
        .billing-section {
            display: table;
            width: 100%;
            margin: 30px 0;
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }
        
        .bill-to, .bill-from {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .bill-details {
            font-size: 12px;
            line-height: 1.6;
        }
        
        .bill-details strong {
            display: block;
            margin-bottom: 3px;
            color: #1f2937;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        th {
            background: #1f2937;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        th.text-right, td.text-right {
            text-align: right;
        }
        
        th.text-center, td.text-center {
            text-align: center;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .totals-section {
            margin-top: 20px;
            text-align: right;
        }
        
        .totals-table {
            display: inline-block;
            min-width: 300px;
        }
        
        .totals-row {
            display: table;
            width: 100%;
            padding: 8px 0;
        }
        
        .totals-label {
            display: table-cell;
            text-align: left;
            font-size: 12px;
            color: #6b7280;
        }
        
        .totals-value {
            display: table-cell;
            text-align: right;
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .total-amount {
            background: #1f2937;
            color: white;
            padding: 12px;
            margin-top: 10px;
            border-radius: 4px;
        }
        
        .total-amount .totals-label,
        .total-amount .totals-value {
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        
        .payment-info {
            background: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 30px 0;
            border-radius: 4px;
        }
        
        .payment-info-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .payment-info-content {
            font-size: 11px;
            color: #1e3a8a;
            line-height: 1.6;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-issued {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-draft {
            background: #f3f4f6;
            color: #374151;
        }
        
        .notes {
            margin: 30px 0;
            padding: 15px;
            background: #f9fafb;
            border-radius: 4px;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #1f2937;
            font-size: 13px;
        }
        
        .notes-content {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @if(!empty($company['logo_url']))
                    <img src="{{ $company['logo_url'] }}" alt="{{ $company['name'] }}" class="logo">
                @endif
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-details">
                    {{ $company['address'] }}<br>
                    {{ $company['city'] }}, {{ $company['state'] }} {{ $company['postal_code'] }}<br>
                    {{ $company['country'] }}<br>
                    <br>
                    Phone: {{ $company['phone'] }}<br>
                    Email: {{ $company['email'] }}<br>
                    @if(!empty($company['tax_id']))
                        Tax ID: {{ $company['tax_id'] }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-meta">
                    <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Date:</strong> {{ $invoice->created_at->format('M d, Y') }}<br>
                    @if($invoice->issued_at)
                        <strong>Issued:</strong> {{ $invoice->issued_at->format('M d, Y') }}<br>
                    @endif
                    @if($invoice->due_date)
                        <strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}<br>
                    @endif
                    <br>
                    <span class="status-badge status-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Billing Information -->
        <div class="billing-section">
            <div class="bill-from">
                <div class="section-title">From</div>
                <div class="bill-details">
                    <strong>{{ $company['name'] }}</strong>
                    {{ $company['address'] }}<br>
                    {{ $company['city'] }}, {{ $company['state'] }} {{ $company['postal_code'] }}<br>
                    {{ $company['country'] }}
                </div>
            </div>
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="bill-details">
                    <strong>{{ $customer['name'] }}</strong>
                    {{ $customer['email'] }}<br>
                    @if(!empty($customer['phone']))
                        {{ $customer['phone'] }}<br>
                    @endif
                    @if(!empty($customer['address']))
                        {{ $customer['address'] }}<br>
                        @if(!empty($customer['city']))
                            {{ $customer['city'] }}, {{ $customer['state'] }} {{ $customer['postal_code'] }}<br>
                        @endif
                        {{ $customer['country'] }}
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Line Items -->
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lineItems as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td class="text-center">{{ $item['quantity'] }}</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format($item['unit_price'], 2) }}</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format($item['amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-table">
                <div class="totals-row">
                    <div class="totals-label">Subtotal:</div>
                    <div class="totals-value">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</div>
                </div>
                
                @if($invoice->tax_amount > 0)
                <div class="totals-row">
                    <div class="totals-label">Tax ({{ number_format($invoice->tax_rate, 2) }}%):</div>
                    <div class="totals-value">{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</div>
                </div>
                @endif
                
                @if($invoice->discount_amount > 0)
                <div class="totals-row">
                    <div class="totals-label">Discount:</div>
                    <div class="totals-value">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</div>
                </div>
                @endif
                
                <div class="totals-row total-amount">
                    <div class="totals-label">TOTAL AMOUNT:</div>
                    <div class="totals-value">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</div>
                </div>
            </div>
        </div>
        
        <!-- Payment Information -->
        @if($invoice->status === 'paid' && $invoice->paid_at)
        <div class="payment-info">
            <div class="payment-info-title">✓ Payment Received</div>
            <div class="payment-info-content">
                This invoice was paid on {{ $invoice->paid_at->format('M d, Y \a\t g:i A') }}<br>
                @if(!empty($invoice->metadata['payment_method']))
                    Payment Method: {{ ucfirst($invoice->metadata['payment_method']) }}<br>
                @endif
                @if(!empty($invoice->metadata['transaction_id']))
                    Transaction ID: {{ $invoice->metadata['transaction_id'] }}
                @endif
            </div>
        </div>
        @endif
        
        <!-- Terms & Notes -->
        @if($invoice->terms || $invoice->notes)
        <div class="notes">
            @if($invoice->terms)
                <div class="notes-title">Terms & Conditions</div>
                <div class="notes-content">{{ $invoice->terms }}</div>
            @endif
            
            @if($invoice->notes)
                <div class="notes-title" style="margin-top: 15px;">Notes</div>
                <div class="notes-content">{{ $invoice->notes }}</div>
            @endif
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top: 10px;">
                This invoice was generated automatically on {{ now()->format('M d, Y \a\t g:i A') }}
                @if($invoice->version > 1)
                    | Version {{ $invoice->version }}
                @endif
            </p>
        </div>
    </div>
</body>
</html>
