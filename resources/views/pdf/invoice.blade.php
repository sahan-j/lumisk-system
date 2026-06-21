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
    background: #6d5cff;
    border-radius: 0.8em;
    color: #ffffff;
    font-size: 1.9em; font-weight: bold;
    text-align: center; line-height: 3.6em;
    vertical-align: middle;
    margin-right: 10px;
}
.logo-img {
    height: 3.6em; width: auto; max-width: 65mm;
    vertical-align: middle;
    margin-right: 10px;
}
.brand {
    display: inline-block;
    vertical-align: middle;
    font-size: 1.6em; font-weight: bold;
    color: #0f172a;
    letter-spacing: 0.3px;
}
.contacts {
    margin-top: 12px;
    font-size: 0.9em;
    color: #6b7280;
    line-height: 1.7;
}
.doc-word {
    font-size: 2.5em; font-weight: bold;
    letter-spacing: 3px;
    color: #0f172a;
    line-height: 1;
}
.doc-num {
    margin-top: 5px;
    font-size: 1.3em; font-weight: bold;
    color: #6d5cff;
}
.pill {
    display: inline-block;
    margin-top: 9px;
    background: rgba(109,92,255,0.1);
    border: 1px solid rgba(109,92,255,0.2);
    color: #6d5cff;
    border-radius: 10px;
    font-size: 0.75em; letter-spacing: 1px;
    text-transform: uppercase;
    padding: 3px 11px; font-weight: bold;
}
.rule {
    clear: both;
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 20px 0;
}

/* ===== BILL TO + DATES ===== */
.bill-left  { float: left;  width: 56%; }
.bill-right { float: right; width: 44%; }
.lbl {
    font-size: 0.8em; letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #9ca3af; font-weight: bold;
}
.cname {
    margin-top: 7px;
    font-size: 1.3em; font-weight: bold;
    color: #0f172a;
}
.cmeta {
    margin-top: 2px;
    font-size: 0.95em; color: #6b7280;
    line-height: 1.65;
}
.dates {
    float: right;
    border-collapse: collapse;
}
.dates td { padding: 3px 0; vertical-align: top; }
.dates .k {
    font-size: 0.8em; letter-spacing: 1px;
    text-transform: uppercase;
    color: #9ca3af;
    text-align: right;
    padding-right: 18px;
}
.dates .v {
    font-size: 1em; font-weight: bold;
    color: #0f172a;
    text-align: right;
}

/* ===== ITEMS ===== */
.items { width: 100%; border-collapse: collapse; margin-top: 26px; }
.items thead td {
    font-size: 0.8em; letter-spacing: 1px;
    text-transform: uppercase;
    color: #6b7280; font-weight: bold;
    padding: 0 9px 9px 9px;
    border-bottom: 2px solid #0f172a;
}
.items tbody td {
    padding: 11px 9px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.items .first { padding-left: 0; }
.items .last  { padding-right: 0; }
.iname { font-size: 1.05em; font-weight: bold; color: #0f172a; }
.idesc {
    display: block;
    margin-top: 3px;
    font-size: 0.9em; color: #9ca3af;
    line-height: 1.45;
}
.cell-num { font-size: 1em; color: #0f172a; }
.cell-dash { font-size: 1em; color: #cbd5e1; }
.cell-free { font-size: 1em; color: #00a8cc; font-weight: bold; }

/* ===== TOTALS ===== */
.totals { float: right; width: 47%; margin-top: 18px; }
.tt { width: 100%; border-collapse: collapse; }
.tt td { padding: 5px 0; font-size: 1em; }
.tt .tl { color: #6b7280; }
.tt .tv { text-align: right; color: #0f172a; }
.grand {
    margin-top: 10px;
    background: rgba(109,92,255,0.08);
    border-radius: 8px;
    padding: 12px 14px;
}
.grand-tbl { width: 100%; border-collapse: collapse; }
.grand-tbl td { vertical-align: middle; }
.gl {
    font-size: 1em; letter-spacing: 1px;
    text-transform: uppercase;
    color: #6d5cff; font-weight: bold;
}
.gv {
    font-size: 1.8em; font-weight: bold;
    color: #0f172a;
    text-align: right;
}

/* ===== PAGE 2: NOTES / BANK / TERMS ===== */
.page-two { clear: both; page-break-before: always; }
.p2-rule { border: none; border-top: 1px solid #e5e7eb; margin: 26px 0; }
.slabel {
    font-size: 0.8em; letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #6d5cff; font-weight: bold;
    margin-bottom: 9px;
}
.col-left  { float: left;  width: 48%; }
.col-right { float: right; width: 48%; }
.kv { width: 100%; border-collapse: collapse; }
.kv td { padding: 3px 0; font-size: 0.95em; vertical-align: top; }
.kv .kk { color: #9ca3af; }
.kv .vv { text-align: right; color: #0f172a; font-weight: bold; }
.body-text { font-size: 0.95em; color: #6b7280; line-height: 1.65; }

/* ===== FOOTER (pinned to bottom of every page) ===== */
.footer {
    position: fixed;
    bottom: 12mm;
    left: 18mm;
    width: 174mm;
    border-top: 1px solid #e5e7eb;
    padding-top: 11px;
}
.fl { float: left; font-style: italic; font-size: 0.9em; color: #9ca3af; }
.fr { float: right; text-align: right; font-size: 0.85em; color: #9ca3af; line-height: 1.5; }
.fr b { color: #6d5cff; }
</style>
</head>
<body>

@php
    $currCode = $invoice->currency_code ?: ($company->currency ?: 'LKR');
    $currency = $invoice->currency_symbol;
    $isForeign = $currCode !== 'LKR';
    $hasBank  = $company->bank_name || $company->bank_account_name || $company->bank_account_number || $company->bank_branch;
    $hasNotes = (bool) $invoice->notes;
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
        <div class="doc-word">INVOICE</div>
        <div class="doc-num mono">{{ $invoice->invoice_number }}</div>
        <div><span class="pill">{{ strtoupper($invoice->status) }}</span></div>
    </div>
</div>

<hr class="rule">

{{-- ===== BILL TO + DATES ===== --}}
<div class="cf">
    <div class="bill-left">
        <div class="lbl">Billed To</div>
        <div class="cname">{{ $invoice->client->name }}</div>
        @if($invoice->client->company_name)<div class="cmeta">{{ $invoice->client->company_name }}</div>@endif
        @if($invoice->client->email)<div class="cmeta">{{ $invoice->client->email }}</div>@endif
        @if($invoice->client->phone)<div class="cmeta">{{ $invoice->client->phone }}</div>@endif
        @if($invoice->client->address)<div class="cmeta">{{ $invoice->client->address }}</div>@endif
    </div>
    <div class="bill-right">
        <table class="dates">
            <tr>
                <td class="k">Issue Date</td>
                <td class="v mono">{{ $invoice->issue_date->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td class="k">Due Date</td>
                <td class="v mono">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}</td>
            </tr>
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
        @foreach($invoice->items as $item)
        @php $isFree = (float)$item->unit_price === 0.0; @endphp
        <tr>
            <td class="first">
                <span class="iname">{{ $item->name }}</span>
                @if($item->description)<span class="idesc">{{ $item->description }}</span>@endif
            </td>
            <td class="c mono cell-num">{{ rtrim(rtrim(number_format((float)$item->quantity, 2), '0'), '.') }}</td>
            <td class="r mono {{ $isFree ? 'cell-dash' : 'cell-num' }}">
                @if($isFree)
                    &mdash;
                @else
                    {{ $currency }} {{ number_format((float)$item->unit_price, 2) }}
                @endif
            </td>
            <td class="r last mono {{ $isFree ? 'cell-free' : 'cell-num' }}">
                @if($isFree)
                    Complimentary
                @else
                    {{ $currency }} {{ number_format((float)$item->total, 2) }}
                @endif
            </td>
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
                <td class="tv mono">{{ $currency }} {{ number_format((float)$invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="tl">Tax ({{ rtrim(rtrim(number_format((float)$invoice->tax_rate, 2), '0'), '.') }}%)</td>
                <td class="tv mono">{{ $currency }} {{ number_format((float)$invoice->tax_amount, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
            <tr>
                <td class="tl">Discount</td>
                <td class="tv mono" style="color:#00a8cc">- {{ $currency }} {{ number_format((float)$invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
        </table>
        <div class="grand">
            <table class="grand-tbl">
                <tr>
                    <td class="gl">Total ({{ $currCode }})</td>
                    <td class="gv mono">{{ number_format((float)$invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>
        @if($isForeign)
        <div style="text-align:right; font-size:0.8em; color:#94a3b8; margin-top:5px;">
            &asymp; Rs {{ number_format((float)$invoice->total_lkr, 2) }} at 1 {{ $currCode }} = Rs {{ number_format((float)$invoice->exchange_rate, 2) }}
        </div>
        @endif
    </div>
</div>

{{-- ===== PAGE 2: PAYMENT DETAILS / NOTES / TERMS ===== --}}
@if($hasNotes || $hasBank || $invoice->terms)
<div class="page-two">
    @if($hasNotes || $hasBank)
    <div class="cf">
        @if($hasBank && $hasNotes)
            <div class="col-left">
                <div class="slabel">Payment Details</div>
                <table class="kv">
                    @if($company->bank_name)<tr><td class="kk">Bank</td><td class="vv">{{ $company->bank_name }}</td></tr>@endif
                    @if($company->bank_account_name)<tr><td class="kk">Account Name</td><td class="vv">{{ $company->bank_account_name }}</td></tr>@endif
                    @if($company->bank_account_number)<tr><td class="kk">Account No.</td><td class="vv mono">{{ $company->bank_account_number }}</td></tr>@endif
                    @if($company->bank_branch)<tr><td class="kk">Branch</td><td class="vv">{{ $company->bank_branch }}</td></tr>@endif
                </table>
            </div>
            <div class="col-right">
                <div class="slabel">Notes</div>
                <div class="body-text">{!! nl2br(e($invoice->notes)) !!}</div>
            </div>
        @elseif($hasBank)
            <div class="slabel">Payment Details</div>
            <table class="kv" style="width:55%">
                @if($company->bank_name)<tr><td class="kk">Bank</td><td class="vv">{{ $company->bank_name }}</td></tr>@endif
                @if($company->bank_account_name)<tr><td class="kk">Account Name</td><td class="vv">{{ $company->bank_account_name }}</td></tr>@endif
                @if($company->bank_account_number)<tr><td class="kk">Account No.</td><td class="vv mono">{{ $company->bank_account_number }}</td></tr>@endif
                @if($company->bank_branch)<tr><td class="kk">Branch</td><td class="vv">{{ $company->bank_branch }}</td></tr>@endif
            </table>
        @else
            <div class="slabel">Notes</div>
            <div class="body-text">{!! nl2br(e($invoice->notes)) !!}</div>
        @endif
    </div>
    @endif

    @if($invoice->terms)
        @if($hasNotes || $hasBank)<hr class="p2-rule">@endif
        <div>
            <div class="slabel">Terms &amp; Conditions</div>
            <div class="body-text">{!! nl2br(e($invoice->terms)) !!}</div>
        </div>
    @endif
</div>
@endif

{{-- ===== FOOTER ===== --}}
<div class="footer cf">
    <div class="fl">Thank you for your business.</div>
    <div class="fr">Generated {{ now()->format('Y-m-d') }} &nbsp;·&nbsp; <b>{{ $company->name }}</b></div>
</div>

</body>
</html>
