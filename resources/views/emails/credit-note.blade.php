<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #0f172a; background: #f8fafc; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
  .header { background: #ef4444; padding: 24px 32px; }
  .header-logo { font-size: 20px; font-weight: bold; color: #ffffff; letter-spacing: -0.5px; }
  .header-sub { font-size: 12px; color: #fee2e2; margin-top: 2px; letter-spacing: 1px; text-transform: uppercase; }
  .body { padding: 32px; }
  .message { font-size: 14px; color: #374151; line-height: 1.7; }
  .detail-box { background: #fef2f2; border: 1px solid #fecaca; border-left: 3px solid #ef4444; border-radius: 4px; padding: 16px 20px; margin: 24px 0; }
  .detail-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
  .detail-label { color: #64748b; }
  .detail-value { font-weight: bold; color: #0f172a; }
  .total-row { border-top: 1px solid #fecaca; padding-top: 10px; margin-top: 10px; }
  .total-row .detail-label { font-size: 14px; font-weight: 600; color: #0f172a; }
  .total-row .detail-value { color: #ef4444; font-size: 16px; }
  .reason-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 14px 20px; margin: 20px 0; font-size: 13px; color: #475569; line-height: 1.6; }
  .footer { padding: 16px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-logo">{{ $company->name }}</div>
    <div class="header-sub">Credit Note</div>
  </div>
  <div class="body">
    <div class="message">
      Dear {{ $creditNote->client->name ?? 'Customer' }},<br><br>
      A credit note has been issued to your account. The details are below and the PDF is attached for your records.
    </div>
    <div class="detail-box">
      <div class="detail-row">
        <span class="detail-label">Credit Note</span>
        <span class="detail-value">{{ $creditNote->credit_note_number }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Issue Date</span>
        <span class="detail-value">{{ $creditNote->issue_date->format('M d, Y') }}</span>
      </div>
      @if($creditNote->invoice)
      <div class="detail-row">
        <span class="detail-label">Reference Invoice</span>
        <span class="detail-value">{{ $creditNote->invoice->invoice_number }}</span>
      </div>
      @endif
      <div class="detail-row total-row">
        <span class="detail-label">Credit Amount</span>
        <span class="detail-value">{{ $company->currency ?: 'LKR' }} {{ number_format((float)$creditNote->total, 2) }}</span>
      </div>
    </div>

    <div class="reason-box">
      <strong>Reason for Credit Note</strong><br>
      {{ $creditNote->reason }}
    </div>

    <div class="message" style="font-size:13px; color:#64748b;">
      This credit note has been applied to your account. No payment is required.
      Log in to your portal to view full details and download the document.
    </div>
  </div>
  <div class="footer">
    {{ $company->name }}
    @if($company->email) &nbsp;·&nbsp; {{ $company->email }} @endif
    @if($company->website) &nbsp;·&nbsp; {{ $company->website }} @endif
  </div>
</div>
</body>
</html>
