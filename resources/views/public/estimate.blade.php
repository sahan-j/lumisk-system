<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Estimate {{ $record->estimate_number }} — {{ $company->name }}</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #0f172a; }
    .container { max-width: 680px; margin: 0 auto; background: #fff;
                 border-radius: 12px; padding: 32px; border: 1px solid #e2e8f0; }
    .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
    .logo { font-size: 20px; font-weight: bold; color: #0f172a; }
    .logo span { display: inline-block; width: 28px; height: 28px;
                 background: linear-gradient(135deg, #00d4ff, #6d5cff);
                 border-radius: 6px; color: #fff; text-align: center;
                 line-height: 28px; font-size: 14px; margin-right: 7px; }
    .badge { padding: 3px 10px; border-radius: 20px; font-size: 11px;
             font-weight: 600; background: rgba(109,92,255,0.1); color: #6d5cff; }
    .download-btn { display: block; width: 100%; padding: 14px; text-align: center;
                    background: linear-gradient(135deg, #00d4ff, #6d5cff);
                    color: #fff; border-radius: 8px; text-decoration: none;
                    font-weight: 600; font-size: 15px; margin: 24px 0; }
    .section-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px;
                     color: #6d5cff; font-weight: 600; margin-bottom: 6px; }
    .muted { color: #64748b; font-size: 13px; line-height: 1.6; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    thead td { background: #6d5cff; color: #fff; padding: 8px 10px;
               font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 8px 10px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
    .total-box { background: #f8fafc; border: 1px solid #e2e8f0;
                 border-radius: 8px; padding: 16px; max-width: 260px;
                 margin-left: auto; }
    .total-row { display: flex; justify-content: space-between;
                 font-size: 13px; padding: 3px 0; }
    .grand { font-size: 16px; font-weight: bold; color: #0f172a;
             border-top: 2px solid #6d5cff; padding-top: 8px; margin-top: 6px; }
    .footer { text-align: center; color: #94a3b8; font-size: 12px; margin-top: 24px; }
    .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
  </style>
</head>
<body>
<div class="container">

  <div class="header">
    <div>
      <div class="logo"><span>L</span>{{ $company->name ?? 'Lumisk' }}</div>
      <div class="muted" style="margin-top:4px;">
        {{ $company->email }}<br>{{ $company->phone }}
      </div>
    </div>
    <div style="text-align:right">
      <div style="font-size:11px;color:#6d5cff;font-weight:600;text-transform:uppercase;letter-spacing:1px">Estimate</div>
      <div style="font-size:22px;font-weight:bold;">{{ $record->estimate_number }}</div>
      <span class="badge">{{ strtoupper($record->status) }}</span>
    </div>
  </div>

  <a href="{{ route('public.view', ['token' => request()->route('token')]) }}?download=1" class="download-btn">
    ⬇ Download PDF
  </a>

  <div class="meta">
    <div>
      <div class="section-label">Prepared For</div>
      <div style="font-weight:600;font-size:14px;">{{ $record->client?->name }}</div>
      <div class="muted">{{ $record->client?->email }}</div>
      <div class="muted">{{ $record->client?->phone }}</div>
    </div>
    <div style="text-align:right">
      <div class="muted">Issue Date: <strong>{{ $record->issue_date?->format('M d, Y') ?? '—' }}</strong></div>
      <div class="muted">Expiry Date: <strong>{{ $record->expiry_date?->format('M d, Y') ?? '—' }}</strong></div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <td style="width:55%">Description</td>
        <td style="text-align:right">Qty</td>
        <td style="text-align:right">Unit Price</td>
        <td style="text-align:right">Amount</td>
      </tr>
    </thead>
    <tbody>
      @foreach($record->items as $item)
      <tr>
        <td>
          <div style="font-weight:600;">{{ $item->name }}</div>
          @if($item->description)
          <div style="font-size:12px;color:#64748b;">{{ $item->description }}</div>
          @endif
        </td>
        <td style="text-align:right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
        <td style="text-align:right">
          @if($item->unit_price == 0) — @else {{ money($item->unit_price) }} @endif
        </td>
        <td style="text-align:right">
          @if($item->unit_price == 0)
          <span style="color:#0891b2;font-weight:600;">Free</span>
          @else
          {{ money($item->total) }}
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="total-box">
    <div class="total-row"><span style="color:#64748b">Subtotal</span><span>{{ money($record->subtotal) }}</span></div>
    <div class="total-row"><span style="color:#64748b">Tax ({{ rtrim(rtrim(number_format($record->tax_rate, 2), '0'), '.') }}%)</span><span>{{ money($record->tax_amount) }}</span></div>
    @if($record->discount_amount > 0)
    <div class="total-row"><span style="color:#64748b">Discount</span><span style="color:#0891b2">−{{ money($record->discount_amount) }}</span></div>
    @endif
    <div class="total-row grand"><span>Total</span><span>{{ money($record->total) }}</span></div>
  </div>

  @if($record->notes)
  <div style="margin-top:20px;">
    <div class="section-label">Notes</div>
    <div class="muted">{!! nl2br(e($record->notes)) !!}</div>
  </div>
  @endif

  <div class="footer">
    {{ $company->name }} · {{ $company->website ?? 'lumisktechnology.com' }}
  </div>
</div>
</body>
</html>
