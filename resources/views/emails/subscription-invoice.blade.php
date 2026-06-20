<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #0f172a; background: #f8fafc; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
  .header { background: linear-gradient(135deg, #00d4ff, #6d5cff); padding: 24px 32px; }
  .header-logo { font-size: 20px; font-weight: bold; color: #ffffff; letter-spacing: -0.5px; }
  .body { padding: 32px; }
  .message { font-size: 14px; color: #374151; line-height: 1.7; }
  .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #6d5cff; border-radius: 4px; padding: 16px 20px; margin: 24px 0; }
  .detail-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
  .detail-label { color: #64748b; }
  .detail-value { font-weight: bold; color: #0f172a; }
  .total-row { border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 10px; }
  .total-row .detail-label { font-size: 14px; font-weight: 600; color: #0f172a; }
  .total-row .detail-value { color: #6d5cff; font-size: 16px; }
  .bank-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 14px 20px; margin: 20px 0; font-size: 12px; color: #475569; line-height: 1.6; }
  .footer { padding: 16px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-logo">Lumisk.</div>
  </div>
  <div class="body">
    <div class="message">
      Dear {{ $invoice->client->name ?? 'Customer' }},<br><br>
      This is an auto-generated invoice for your subscription
      <strong>{{ $subscription->name }}</strong> ({{ $subscription->billing_cycle_label }}).
      Please find the invoice details below and the PDF attached.
    </div>
    <div class="detail-box">
      <div class="detail-row">
        <span class="detail-label">Subscription</span>
        <span class="detail-value">{{ $subscription->subscription_number }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Invoice Number</span>
        <span class="detail-value">{{ $invoice->invoice_number }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Issue Date</span>
        <span class="detail-value">{{ $invoice->issue_date->format('M d, Y') }}</span>
      </div>
      @if($invoice->due_date)
      <div class="detail-row">
        <span class="detail-label">Due Date</span>
        <span class="detail-value">{{ $invoice->due_date->format('M d, Y') }}</span>
      </div>
      @endif
      <div class="detail-row total-row">
        <span class="detail-label">Total Amount</span>
        <span class="detail-value">{{ $company->currency ?: 'LKR' }} {{ number_format((float)$invoice->total, 2) }}</span>
      </div>
    </div>

    @if($company->bank_name || $company->bank_account_number)
    <div class="bank-box">
      <strong>Payment Details</strong><br>
      @if($company->bank_name) Bank: {{ $company->bank_name }}<br> @endif
      @if($company->bank_account_name) Account Name: {{ $company->bank_account_name }}<br> @endif
      @if($company->bank_account_number) Account Number: {{ $company->bank_account_number }}<br> @endif
      @if($company->bank_branch) Branch: {{ $company->bank_branch }} @endif
    </div>
    @endif

    <div class="message" style="font-size:13px; color:#64748b;">
      Log in to your portal to view full details and download the invoice.
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
