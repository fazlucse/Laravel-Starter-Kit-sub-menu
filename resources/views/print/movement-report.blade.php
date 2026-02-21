<!DOCTYPE html>
<html>
<head>
    <title>Movement Report</title>
    <style>
        @page { size: A4; margin: 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; table-layout: fixed; }
        th, td { border: 1px solid #999; padding: 6px; word-wrap: break-word; vertical-align: top; }
        th { background-color: #f0f0f0; text-transform: uppercase; font-size: 10px; }

        .header-section { margin-bottom: 25px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .footer-sig { margin-top: 80px; width: 100%; }
        .sig-box { width: 30%; float: left; text-align: center; }
        .sig-line { border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px; font-weight: bold; }

        @media print {
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

<div class="text-center">
    <button class="btn-print" onclick="window.print()" style="padding:10px; margin-bottom:20px; cursor: pointer;">Confirm Print</button>
</div>

<div class="header-section text-center">
    <h2 style="margin:0; padding:0;">MOVEMENT REGISTER REPORT</h2>
    <p style="margin:5px 0;">Print Date: {{ $printDate }}</p>
</div>

<table>
    <thead>
    <tr>
        <th style="width: 30px;">SL</th>
        <th style="width: 70px;">Date</th>
        <th>Route (From - To)</th>
        <th>Purpose & Visit Detail</th>
        <th style="width: 100px;">Transport</th>
        <th style="width: 80px;">Bill (BDT)</th>
    </tr>
    </thead>
    <tbody>
    @php $total = 0; @endphp
    @foreach($movements as $index => $m)
        @php $total += $m->conveyance_amount; @endphp
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-center">{{ \Carbon\Carbon::parse($m->movement_started_at)->format('d-M-y') }}</td>
            <td>
                <span class="font-bold">From:</span> {{ $m->origin_location_name }} <br>
                <span class="font-bold">To:</span> {{ $m->destination_location_name ?? 'N/A' }}
            </td>
            <td>
                <div class="font-bold">{{ collect($m->purpose)->pluck('name')->implode(', ') }}</div>
                @if($m->visit_type === 'Customer')
                    <small style="color: #555;">Customer: {{ $m->customer_name }}</small>
                @elseif($m->bill_remarks)
                    <small style="color: #555;">{{ $m->bill_remarks }}</small>
                @endif
            </td>
            <td>{{ collect($m->transport_mode)->pluck('name')->implode(', ') }}</td>
            <td class="text-right font-bold">{{ number_format($m->conveyance_amount, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr style="background: #f9f9f9;">
        <td colspan="5" class="text-right font-bold">Total Conveyance Amount:</td>
        <td class="text-right font-bold" style="border-bottom: 4px double #000;">{{ number_format($total, 2) }}</td>
    </tr>
    </tfoot>
</table>

<div class="footer-sig">
    <div class="sig-box">
        <p>{{ $movements->first()->created_by_name ?? 'Employee' }}</p>
        <div class="sig-line">Prepared By</div>
    </div>
    <div class="sig-box">
        <p>&nbsp;</p> <div class="sig-line">Checked By</div>
    </div>
    <div class="sig-box">
        <p>{{ $movements->first()->approved_by_name ?? 'Pending' }}</p>
        <div class="sig-line">Approved By</div>
    </div>
</div>

<div style="clear: both;"></div>

</body>
</html>
