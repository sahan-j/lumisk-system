@php $fs = (int) ($company->pdf_font_size ?: 10); @endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: {{ $fs }}px;
    color: #334155;
    background: #ffffff;
    margin: 18mm 18mm 24mm 18mm;
}
.cf:after { content: ""; display: table; clear: both; }
.mono { font-family: DejaVu Sans Mono, monospace; }
.c { text-align: center; }
.r { text-align: right; }

/* ===== HEADER ===== */
.hd-left  { float: left;  width: 58%; }
.hd-right { float: right; width: 42%; text-align: right; }
.badge {
    display: inline-block;
    width: 3.6em; height: 3.6em;
    background: #ef4444;
    border-radius: 0.8em;
    color: #ffffff;
    font-size: 1.9em; font-weight: bold;
    text-align: center; line-height: 3.6em;
    vertical-align: middle;
    margin-right: 10px;
}
.logo-img { height: 3.6em; width: auto; max-width: 65mm; vertical-align: middle; margin-right: 10px; }
.brand { display: inline-block; vertical-align: middle; font-size: 1.6em; font-weight: bold; color: #0f172a; letter-spacing: 0.3px; }
.contacts { margin-top: 12px; font-size: 0.9em; color: #6b7280; line-height: 1.7; }
.doc-word { font-size: 2.3em; font-weight: bold; letter-spacing: 2px; color: #ef4444; line-height: 1; }
.doc-num { margin-top: 5px; font-size: 1.3em; font-weight: bold; color: #ef4444; }
.pill {
    display: inline-block; margin-top: 9px;
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.2);
    color: #ef4444; border-radius: 10px;
    font-size: 0.75em; letter-spacing: 1px;
    text-transform: uppercase; padding: 3px 11px; font-weight: bold;
}
.rule { clear: both; border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }

/* ===== BILL TO + DATES ===== */
.bill-left  { float: left;  width: 56%; }
.bill-right { float: right; width: 44%; }
.lbl { font-size: 0.8em; letter-spacing: 1.5px; text-transform: uppercase; color: #9ca3af; font-weight: bold; }
.cname { margin-top: 7px; font-size: 1.3em; font-weight: bold; color: #0f172a; }
.cmeta { margin-top: 2px; font-size: 0.95em; color: #6b7280; line-height: 1.65; }
.dates { float: right; border-collapse: collapse; }
.dates td { padding: 3px 0; vertical-align: top; }
.dates .k { font-size: 0.8em; letter-spacing: 1px; text-transform: uppercase; color: #9ca3af; text-align: right; padding-right: 18px; }
.dates .v { font-size: 1em; font-weight: bold; color: #0f172a; text-align: right; }

/* ===== ITEMS ===== */
.items { width: 100%; border-collapse: collapse; margin-top: 26px; }
.items thead td { font-size: 0.8em; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; font-weight: bold; padding: 0 9px 9px 9px; border-bottom: 2px solid #ef4444; }
.items tbody td { padding: 11px 9px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.items .first { padding-left: 0; }
.items .last  { padding-right: 0; }
.iname { font-size: 1.05em; font-weight: bold; color: #0f172a; }
.idesc { display: block; margin-top: 3px; font-size: 0.9em; color: #9ca3af; line-height: 1.45; }
.cell-num { font-size: 1em; color: #0f172a; }

/* ===== TOTALS ===== */
.totals { float: right; width: 47%; margin-top: 18px; }
.tt { width: 100%; border-collapse: collapse; }
.tt td { padding: 5px 0; font-size: 1em; }
.tt .tl { color: #6b7280; }
.tt .tv { text-align: right; color: #0f172a; }
.grand { margin-top: 10px; background: rgba(239,68,68,0.08); border-radius: 8px; padding: 12px 14px; }
.grand-tbl { width: 100%; border-collapse: collapse; }
.grand-tbl td { vertical-align: middle; }
.gl { font-size: 1em; letter-spacing: 1px; text-transform: uppercase; color: #ef4444; font-weight: bold; }
.gv { font-size: 1.8em; font-weight: bold; color: #0f172a; text-align: right; }

/* ===== REASON / NOTES ===== */
.reason-box {
    clear: both;
    background: #fef2f2; border: 1px solid #fecaca; border-left: 3px solid #ef4444;
    border-radius: 4px; padding: 12px 14px; margin-top: 22px;
}
.reason-lbl { font-size: 0.8em; text-transform: uppercase; letter-spacing: 1.5px; color: #ef4444; font-weight: bold; margin-bottom: 5px; }
.reason-val { font-size: 1em; color: #0f172a; }
.reason-ref { font-size: 0.9em; color: #64748b; margin-top: 5px; }
.notes-blk { margin-top: 20px; }
.slabel { font-size: 0.8em; letter-spacing: 1.5px; text-transform: uppercase; color: #ef4444; font-weight: bold; margin-bottom: 9px; }
.body-text { font-size: 0.95em; color: #6b7280; line-height: 1.65; }

/* ===== FOOTER ===== */
.footer { position: fixed; bottom: 12mm; left: 18mm; width: 174mm; border-top: 1px solid #e5e7eb; padding-top: 11px; }
.fl { float: left; font-style: italic; font-size: 0.9em; color: #9ca3af; }
.fr { float: right; text-align: right; font-size: 0.85em; color: #9ca3af; line-height: 1.5; }
.fr b { color: #ef4444; }
</style>
</head>
<body>

@php
    $currency = $company->currency ?: 'LKR';
    $logoSrc = null;
    if ($company->logo) {
        $logoPath = storage_path('app/public/' . $company->logo);
        if (is_file($logoPath)) {
            $logoSrc = 'data:' . (@mime_content_type($logoPath) ?: 'image/png') . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }
@endphp

{{-- ===== HEADER ===== --}}
<div class="cf">
    <div class="hd-left">
        @if($logoSrc)<img src="{{ $logoSrc }}" class="logo-img" alt="{{ $company->name }}">@else<span class="badge">L</span>@endif<span class="brand">{{ $company->name }}</span>
        <div class="contacts">
            @if($company->website){{ str_replace(['https://', 'http://'], '', rtrim($company->website, '/')) }}<br>@endif
            @if($company->email){{ $company->email }}@endif@if($company->email && $company->phone) &nbsp;·&nbsp; @endif@if($company->phone){{ $company->phone }}@endif
            @if($company->address)<br>{{ $company->address }}@endif
        </div>
    </div>
    <div class="hd-right">
        <div class="doc-word">CREDIT NOTE</div>
        <div class="doc-num mono">{{ $creditNote->credit_note_number }}</div>
        <div><span class="pill">{{ strtoupper($creditNote->status) }}</span></div>
    </div>
</div>

<hr class="rule">

{{-- ===== CREDIT TO + DATES ===== --}}
<div class="cf">
    <div class="bill-left">
        <div class="lbl">Credit To</div>
        <div class="cname">{{ $creditNote->client->name }}</div>
        @if($creditNote->client->company_name)<div class="cmeta">{{ $creditNote->client->company_name }}</div>@endif
        @if($creditNote->client->email)<div class="cmeta">{{ $creditNote->client->email }}</div>@endif
        @if($creditNote->client->phone)<div class="cmeta">{{ $creditNote->client->phone }}</div>@endif
        @if($creditNote->client->address)<div class="cmeta">{{ $creditNote->client->address }}</div>@endif
    </div>
    <div class="bill-right">
        <table class="dates">
            <tr>
                <td class="k">Issue Date</td>
                <td class="v mono">{{ $creditNote->issue_date->format('Y-m-d') }}</td>
            </tr>
            @if($creditNote->invoice)
            <tr>
                <td class="k">Ref. Invoice</td>
                <td class="v mono">{{ $creditNote->invoice->invoice_number }}</td>
            </tr>
            @endif
        </table>
    </div>
</div>

{{-- ===== ITEMS ===== --}}
<table class="items">
    <thead>
        <tr>
            <td class="first" style="width:52%">Item</td>
            <td class="c" style="width:10%">Qty</td>
            <td class="r" style="width:19%">Unit Price</td>
            <td class="r last" style="width:19%">Amount</td>
        </tr>
    </thead>
    <tbody>
        @foreach($creditNote->items as $item)
        <tr>
            <td class="first">
                <span class="iname">{{ $item->name }}</span>
                @if($item->description)<span class="idesc">{{ $item->description }}</span>@endif
            </td>
            <td class="c mono cell-num">{{ rtrim(rtrim(number_format((float)$item->quantity, 2), '0'), '.') }}</td>
            <td class="r mono cell-num">{{ $currency }} {{ number_format((float)$item->unit_price, 2) }}</td>
            <td class="r last mono cell-num">{{ $currency }} {{ number_format((float)$item->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ===== TOTALS ===== --}}
<div class="cf">
    <div class="totals">
        <table class="tt">
            <tr>
                <td class="tl">Subtotal</td>
                <td class="tv mono">{{ $currency }} {{ number_format((float)$creditNote->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="tl">Tax ({{ rtrim(rtrim(number_format((float)$creditNote->tax_rate, 2), '0'), '.') }}%)</td>
                <td class="tv mono">{{ $currency }} {{ number_format((float)$creditNote->tax_amount, 2) }}</td>
            </tr>
        </table>
        <div class="grand">
            <table class="grand-tbl">
                <tr>
                    <td class="gl">Credit Total ({{ $currency }})</td>
                    <td class="gv mono">{{ number_format((float)$creditNote->total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{-- ===== REASON ===== --}}
<div class="reason-box">
    <div class="reason-lbl">Reason for Credit Note</div>
    <div class="reason-val">{{ $creditNote->reason }}</div>
    @if($creditNote->invoice)
        <div class="reason-ref">Reference Invoice: {{ $creditNote->invoice->invoice_number }}</div>
    @endif
</div>

@if($creditNote->notes)
<div class="notes-blk">
    <div class="slabel">Notes</div>
    <div class="body-text">{!! nl2br(e($creditNote->notes)) !!}</div>
</div>
@endif

{{-- ===== FOOTER ===== --}}
<div class="footer cf">
    <div class="fl">This is a credit note — no payment required.</div>
    <div class="fr">Generated {{ now()->format('Y-m-d') }} &nbsp;·&nbsp; <b>{{ $company->name }}</b></div>
</div>

</body>
</html>
