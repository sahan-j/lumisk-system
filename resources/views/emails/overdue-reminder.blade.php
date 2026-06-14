<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #0f172a; background: #f8fafc; margin: 0; }
  .wrapper { max-width: 520px; margin: 32px auto; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
  .header { padding: 24px 32px; background: linear-gradient(135deg, #ef4444, #dc2626); }
  .header-logo { font-size: 20px; font-weight: bold; color: #fff; }
  .header-sub { font-size: 13px; color: rgba(255,255,255,0.85); margin-top: 4px; }
  .body { padding: 32px; }
  .alert-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; }
  .alert-box p { font-size: 13px; color: #dc2626; margin: 0; font-weight: 500; }
  .message { font-size: 14px; color: #374151; line-height: 1.7; margin-bottom: 20px; }
  .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #ef4444; border-radius: 4px; padding: 14px 16px; margin-bottom: 20px; }
  .detail-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
  .detail-label { color: #64748b; }
  .detail-value { font-weight: 600; color: #0f172a; }
  .overdue-value { font-weight: 700; color: #ef4444; }
  .total-row { border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 8px; }
  .bank-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; margin-top: 16px; }
  .bank-title { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #6d5cff; font-weight: 700; margin-bottom: 8px; }
  .bank-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; }
  .bank-label { color: #94a3b8; }
  .bank-value { font-weight: 600; color: #0f172a; }
  .footer { padding: 16px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-logo">{{ $company->name ?? 'Lumisk.' }}</div>
    <div class="header-sub">
      @if($daysOverdue > 0)
        Payment Overdue — {{ $daysOverdue }} day(s) past due
      @else
        Payment Due — Action Required
      @endif
    </div>
  </div>

  <div class="body">
    @if($daysOverdue > 0)
    <div class="alert-box">
      <p>&#9888; Your invoice is {{ $daysOverdue }} day(s) overdue. Please arrange payment at your earliest convenience.</p>
    </div>
    @endif

    <div class="message">
      Dear {{ $invoice->client->name }},<br><br>
      @if($daysOverdue > 0)
        This is a reminder that Invoice <strong>{{ $invoice->invoice_number }}</strong>
        was due on <strong>{{ $invoice->due_date->format('M d, Y') }}</strong> and remains unpaid.
        Please make payment as soon as possible to avoid any disruption to your services.
      @else
        Your invoice <strong>{{ $invoice->invoice_number }}</strong> is now overdue.
        Please arrange payment at your earliest convenience.
      @endif
    </div>

    <div class="detail-box">
      <div class="detail-row">
        <span class="detail-label">Invoice Number</span>
        <span class="detail-value">{{ $invoice->invoice_number }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Due Date</span>
        <span class="overdue-value">{{ $invoice->due_date->format('M d, Y') }}</span>
      </div>
      @if($daysOverdue > 0)
      <div class="detail-row">
        <span class="detail-label">Days Overdue</span>
        <span class="overdue-value">{{ $daysOverdue }} days</span>
      </div>
      @endif
      <div class="detail-row total-row">
        <span class="detail-label">Outstanding Amount</span>
        <span class="overdue-value" style="font-size:16px;">
          {{ money($invoice->outstanding_balance) }}
        </span>
      </div>
    </div>

    @if($company->bank_name)
    <div class="bank-box">
      <div class="bank-title">Bank Transfer Details</div>
      <div class="bank-row">
        <span class="bank-label">Bank</span>
        <span class="bank-value">{{ $company->bank_name }}</span>
      </div>
      @if($company->bank_account_name)
      <div class="bank-row">
        <span class="bank-label">Account Name</span>
        <span class="bank-value">{{ $company->bank_account_name }}</span>
      </div>
      @endif
      @if($company->bank_account_number)
      <div class="bank-row">
        <span class="bank-label">Account No</span>
        <span class="bank-value">{{ $company->bank_account_number }}</span>
      </div>
      @endif
      @if($company->bank_branch)
      <div class="bank-row">
        <span class="bank-label">Branch</span>
        <span class="bank-value">{{ $company->bank_branch }}</span>
      </div>
      @endif
    </div>
    @endif

    <p style="font-size:13px; color:#64748b; margin-top:16px;">
      If you have already made payment, please disregard this email or contact us to confirm your payment.
    </p>
  </div>

  <div class="footer">
    {{ $company->name }}
    @if($company->email) &nbsp;&middot;&nbsp; {{ $company->email }} @endif
    @if($company->phone) &nbsp;&middot;&nbsp; {{ $company->phone }} @endif
  </div>
</div>
</body>
</html>
