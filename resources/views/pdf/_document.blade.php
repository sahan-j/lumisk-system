@php
    /** @var \App\Models\Company $company */
    $currency = $company->currency ?: 'LKR';
    $fmt = fn ($v) => $currency . ' ' . number_format((float) $v, 2);
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');

    $logoData = null;
    if ($company->logo) {
        $logoPath = storage_path('app/public/' . $company->logo);
        if (is_file($logoPath)) {
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION)) ?: 'png';
            $logoData = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 15mm 15mm 15mm 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            background: #ffffff;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.5;
        }

        /* ── Header ──────────────────────────────────────── */
        .hdr-left  { float: left;  width: 60%; }
        .hdr-right { float: right; width: 40%; text-align: right; }
        .brand-icon {
            display: inline-block;
            width: 32px; height: 32px;
            background-color: #6d5cff;
            color: #ffffff;
            font-weight: bold; font-size: 15px;
            text-align: center; line-height: 32px;
            border-radius: 6px;
            vertical-align: middle;
        }
        .brand-name {
            font-size: 17px; font-weight: bold; color: #0f172a;
            display: inline-block; vertical-align: middle; margin-left: 9px;
        }
        .company-contact { margin-top: 7px; color: #64748b; font-size: 9.5px; line-height: 1.65; }
        .doc-label { font-size: 8px; letter-spacing: 2px; text-transform: uppercase; color: #6d5cff; font-weight: bold; }
        .doc-number { font-size: 22px; font-weight: bold; font-family: DejaVu Sans Mono, monospace; color: #0f172a; margin-top: 3px; }
        .status-pill {
            display: inline-block;
            background-color: rgba(109,92,255,0.1); color: #6d5cff;
            border: 1px solid rgba(109,92,255,0.2); border-radius: 20px;
            font-size: 8px; letter-spacing: 1px; text-transform: uppercase;
            padding: 3px 10px; font-weight: bold; margin-top: 6px;
        }

        /* ── Accent bar ──────────────────────────────────── */
        .accent-bar { height: 2px; background-color: #6d5cff; margin: 14px 0 18px 0; border-radius: 2px; }

        /* ── Bill-to / dates ─────────────────────────────── */
        .meta-left  { float: left;  width: 55%; }
        .meta-right { float: right; width: 43%; }
        .section-label { font-size: 8px; letter-spacing: 1.5px; text-transform: uppercase; color: #6d5cff; font-weight: bold; margin-bottom: 5px; }
        .client-name   { font-size: 12px; font-weight: bold; color: #0f172a; }
        .client-detail { font-size: 9.5px; color: #64748b; line-height: 1.6; }
        .date-table    { width: 100%; border-collapse: collapse; }
        .date-lbl      { color: #64748b; font-size: 9.5px; text-align: left; padding: 2px 0; }
        .date-val      { color: #0f172a; font-size: 9.5px; text-align: right; padding: 2px 0; font-family: DejaVu Sans Mono, monospace; }

        /* ── Items table ─────────────────────────────────── */
        .items              { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items thead th     { background-color: #6d5cff; color: #ffffff; font-size: 8.5px; letter-spacing: 0.8px; text-transform: uppercase; font-weight: bold; padding: 8px 10px; text-align: left; }
        .items thead th.r   { text-align: right; }
        .items tbody td     { padding: 9px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .row-even td        { background-color: #f8fafc; }
        .row-odd  td        { background-color: #ffffff; }
        .item-name          { font-weight: bold; color: #0f172a; font-size: 10.5px; }
        .item-desc          { color: #64748b; font-size: 9px; line-height: 1.45; display: block; margin-top: 2px; }
        .num                { font-family: DejaVu Sans Mono, monospace; text-align: right; color: #0f172a; }
        .free-amt           { font-family: DejaVu Sans Mono, monospace; text-align: right; color: #00a8cc; font-weight: bold; }
        tr                  { page-break-inside: avoid; }

        /* ── Totals ──────────────────────────────────────── */
        .totals-outer { float: right; width: 38%; margin-top: 16px; }
        .totals-box   { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; }
        .totals-tbl   { width: 100%; border-collapse: collapse; }
        .t-lbl        { color: #64748b; font-size: 9.5px; padding: 2.5px 0; }
        .t-val        { text-align: right; font-family: DejaVu Sans Mono, monospace; font-size: 9.5px; color: #0f172a; padding: 2.5px 0; }
        .t-grand-lbl  { font-size: 9px; letter-spacing: 1.5px; text-transform: uppercase; color: #6d5cff; font-weight: bold; padding-top: 8px; border-top: 1.5px solid #6d5cff; }
        .t-grand-val  { font-size: 15px; font-weight: bold; font-family: DejaVu Sans Mono, monospace; color: #0f172a; text-align: right; padding-top: 8px; border-top: 1.5px solid #6d5cff; }

        /* ── Notes / Terms ───────────────────────────────── */
        .notes-tbl       { width: 100%; border-collapse: collapse; margin-top: 22px; }
        .notes-tbl td    { vertical-align: top; width: 50%; padding-right: 8px; }
        .notes-tbl td:last-child { padding-right: 0; padding-left: 8px; }
        .note-box        { background-color: #f8fafc; border: 1px solid #e2e8f0; border-left-width: 2px; border-left-color: #6d5cff; border-radius: 8px; padding: 12px 14px; }
        .terms-box       { background-color: #f8fafc; border: 1px solid #e2e8f0; border-left-width: 2px; border-left-color: #00d4ff; border-radius: 8px; padding: 12px 14px; }
        .note-label      { font-size: 8px; letter-spacing: 1.5px; text-transform: uppercase; color: #6d5cff; font-weight: bold; margin-bottom: 6px; display: block; }
        .terms-label     { font-size: 8px; letter-spacing: 1.5px; text-transform: uppercase; color: #00a8cc; font-weight: bold; margin-bottom: 6px; display: block; }
        .note-text       { font-size: 9.5px; color: #64748b; line-height: 1.65; }

        /* ── Footer ──────────────────────────────────────── */
        .footer     { border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 14px; }
        .ft-left    { float: left; }
        .ft-right   { float: right; }
        .ft-thanks  { font-style: italic; font-size: 9px; color: #94a3b8; }
        .ft-site    { font-size: 9px; color: #6d5cff; font-weight: bold; }
    </style>
</head>
<body>

{{-- HEADER --}}
<div class="hdr-left">
    @if ($logoData)
        <img src="{{ $logoData }}" style="max-height: 44px; max-width: 180px; vertical-align: middle;">
    @else
        <span class="brand-icon">L</span><span class="brand-name">Lumisk.</span>
    @endif
    <div class="company-contact">
        @if ($company->email)<div>{{ $company->email }}</div>@endif
        @if ($company->phone)<div>{{ $company->phone }}</div>@endif
        @if ($company->address)<div>{!! nl2br(e($company->address)) !!}</div>@endif
    </div>
</div>
<div class="hdr-right">
    <div class="doc-label">{{ $heading }}</div>
    <div class="doc-number">{{ $number }}</div>
    <div><span class="status-pill">{{ ucfirst($doc->status) }}</span></div>
</div>
<div style="clear:both;"></div>

{{-- ACCENT BAR --}}
<div class="accent-bar"></div>

{{-- BILL-TO + DATES --}}
<div class="meta-left">
    <div class="section-label">{{ strtoupper($recipientLabel) }}</div>
    <div class="client-name">{{ $doc->client?->name }}</div>
    @if ($doc->client?->company_name)<div class="client-detail">{{ $doc->client->company_name }}</div>@endif
    @if ($doc->client?->email)<div class="client-detail">{{ $doc->client->email }}</div>@endif
    @if ($doc->client?->phone)<div class="client-detail">{{ $doc->client->phone }}</div>@endif
    @if ($doc->client?->address)<div class="client-detail">{!! nl2br(e($doc->client->address)) !!}</div>@endif
</div>
<div class="meta-right">
    <table class="date-table">
        <tr>
            <td class="date-lbl">Issue Date</td>
            <td class="date-val">{{ $doc->issue_date?->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td class="date-lbl">{{ $secondDateLabel }}</td>
            <td class="date-val">{{ $secondDate?->format('Y-m-d') ?? '—' }}</td>
        </tr>
    </table>
</div>
<div style="clear:both;"></div>

{{-- LINE ITEMS --}}
<table class="items">
    <thead>
        <tr>
            <th style="width:54%;">Description</th>
            <th class="r" style="width:8%;">Qty</th>
            <th class="r" style="width:18%;">Unit Price</th>
            <th class="r" style="width:20%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($doc->items as $i => $item)
            @php $isFree = (float)$item->unit_price === 0.0; @endphp
            <tr class="{{ $i % 2 === 0 ? 'row-even' : 'row-odd' }}">
                <td>
                    <span class="item-name">{{ $item->name }}</span>
                    @if ($item->description)<span class="item-desc">{{ $item->description }}</span>@endif
                </td>
                <td class="num">{{ $qty($item->quantity) }}</td>
                <td class="num">{{ $isFree ? '—' : $fmt($item->unit_price) }}</td>
                <td class="{{ $isFree ? 'free-amt' : 'num' }}">{{ $isFree ? 'Complimentary' : $fmt($item->total) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- TOTALS --}}
<div class="totals-outer">
    <div class="totals-box">
        <table class="totals-tbl">
            <tr>
                <td class="t-lbl">Subtotal</td>
                <td class="t-val">{{ $fmt($doc->subtotal) }}</td>
            </tr>
            <tr>
                <td class="t-lbl">Tax ({{ $qty($doc->tax_rate) }}%)</td>
                <td class="t-val">{{ $fmt($doc->tax_amount) }}</td>
            </tr>
            @if ($doc->discount_amount > 0)
            <tr>
                <td class="t-lbl">Discount</td>
                <td class="t-val">- {{ $fmt($doc->discount_amount) }}</td>
            </tr>
            @endif
            <tr>
                <td class="t-grand-lbl">Total</td>
                <td class="t-grand-val">{{ $fmt($doc->total) }}</td>
            </tr>
        </table>
    </div>
</div>
<div style="clear:both;"></div>

{{-- NOTES & TERMS --}}
@if ($doc->notes || $doc->terms)
    <table class="notes-tbl">
        <tr>
            @if ($doc->notes && $doc->terms)
                <td>
                    <div class="note-box">
                        <span class="note-label">Notes</span>
                        <div class="note-text">{!! nl2br(e($doc->notes)) !!}</div>
                    </div>
                </td>
                <td>
                    <div class="terms-box">
                        <span class="terms-label">Terms</span>
                        <div class="note-text">{!! nl2br(e($doc->terms)) !!}</div>
                    </div>
                </td>
            @elseif ($doc->notes)
                <td colspan="2">
                    <div class="note-box">
                        <span class="note-label">Notes</span>
                        <div class="note-text">{!! nl2br(e($doc->notes)) !!}</div>
                    </div>
                </td>
            @else
                <td colspan="2">
                    <div class="terms-box">
                        <span class="terms-label">Terms</span>
                        <div class="note-text">{!! nl2br(e($doc->terms)) !!}</div>
                    </div>
                </td>
            @endif
        </tr>
    </table>
@endif

{{-- FOOTER --}}
<div class="footer">
    <div class="ft-left"><span class="ft-thanks">Thank you for your business</span></div>
    <div class="ft-right"><span class="ft-site">lumisktechnology.com</span></div>
    <div style="clear:both;"></div>
</div>

</body>
</html>
