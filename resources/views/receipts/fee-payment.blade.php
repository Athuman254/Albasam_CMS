<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $receipt_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .receipt-title { font-size: 16px; margin: 10px 0; font-weight: bold; }
        .details-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .details-table td { padding: 8px; border: 1px solid #ddd; }
        .details-table .label { font-weight: bold; background: #f5f5f5; width: 30%; }
        .breakdown-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .breakdown-table th { background: #333; color: white; padding: 10px; text-align: left; }
        .breakdown-table td { padding: 8px; border: 1px solid #ddd; }
        .breakdown-table .total-row { background: #f8f9fa; font-weight: bold; }
        .breakdown-table .section-header { background: #e9ecef; font-weight: bold; }
        .amount { text-align: right; }
        .total { font-size: 14px; font-weight: bold; background: #f8f9fa; padding: 10px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
        .signature { margin-top: 40px; border-top: 1px solid #333; padding-top: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-3 { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school['name'] }}</div>
        <div>{{ $school['address'] }}</div>
        <div>Tel: {{ $school['phone'] }} | Email: {{ $school['email'] }}</div>
        <div class="receipt-title">FEE PAYMENT RECEIPT</div>
    </div>

    <!-- Student and Payment Details -->
    <table class="details-table">
        <tr>
            <td class="label">Receipt Number:</td>
            <td><strong>{{ $receipt_number }}</strong></td>
        </tr>
        <tr>
            <td class="label">Student Name:</td>
            <td>{{ $payment->student->full_name }}</td>
        </tr>
        <tr>
            <td class="label">Admission Number:</td>
            <td>{{ $payment->student->admission_number }}</td>
        </tr>
        <tr>
            <td class="label">Class:</td>
            <td>{{ $payment->fee->rank->name }}</td>
        </tr>
        <tr>
            <td class="label">Payment Method:</td>
            <td>{{ strtoupper($payment->payment_method) }}</td>
        </tr>
        <tr>
            <td class="label">Reference Number:</td>
            <td>{{ $payment->reference_number }}</td>
        </tr>
        <tr>
            <td class="label">Payment Date:</td>
            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Verified By:</td>
            <td>{{ $payment->verifiedBy->name ?? 'System' }}</td>
        </tr>
    </table>

    <!-- Fee Breakdown -->
    <h4 style="margin-top: 20px; margin-bottom: 10px;">Fee Breakdown</h4>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount">Amount (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $hasPreviousBalance = false;
                $hasCurrentFees = false;
            @endphp

            <!-- Previous Balances -->
            @foreach($fee_breakdown['breakdown'] as $item)
                @if($item['type'] == 'previous_balance')
                    @if(!$hasPreviousBalance)
                        <tr class="section-header">
                            <td colspan="2">PREVIOUS BALANCES</td>
                        </tr>
                        @php $hasPreviousBalance = true; @endphp
                    @endif
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endif
            @endforeach

            @if($hasPreviousBalance)
                <tr class="total-row">
                    <td><strong>Total Previous Balance</strong></td>
                    <td class="amount"><strong>{{ number_format($fee_breakdown['summary']['total_previous_balance'], 2) }}</strong></td>
                </tr>
            @endif

            <!-- Current Term Fees -->
            @foreach($fee_breakdown['breakdown'] as $item)
                @if($item['type'] == 'current_fee')
                    @if(!$hasCurrentFees)
                        <tr class="section-header">
                            <td colspan="2">CURRENT TERM FEES - {{ $payment->fee->academic_year }} TERM {{ $payment->fee->term }}</td>
                        </tr>
                        @php $hasCurrentFees = true; @endphp
                    @endif
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endif
            @endforeach

            @if($hasCurrentFees)
                <tr class="total-row">
                    <td><strong>Total Current Term Fees</strong></td>
                    <td class="amount"><strong>{{ number_format($fee_breakdown['summary']['total_current_fees'], 2) }}</strong></td>
                </tr>
            @endif

            <!-- Summary Section -->
            <tr class="section-header">
                <td colspan="2">SUMMARY</td>
            </tr>
            <tr>
                <td>Total Balance Before Payment</td>
                <td class="amount">{{ number_format($fee_breakdown['summary']['total_balance_before'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Amount Paid</strong></td>
                <td class="amount" style="color: #28a745; font-weight: bold;">- {{ number_format($fee_breakdown['summary']['amount_paid'], 2) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>NEW BALANCE</strong></td>
                <td class="amount" style="color: #dc3545; font-weight: bold;">{{ number_format($fee_breakdown['summary']['new_balance'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div>This is a computer generated receipt. No signature required.</div>
        <div>Generated on: {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>