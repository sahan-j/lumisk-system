<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #0f172a; background: #f8fafc; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
  .header { background: linear-gradient(135deg, #f59e0b, #f97316); padding: 24px 32px; }
  .header-logo { font-size: 20px; font-weight: bold; color: #ffffff; letter-spacing: -0.5px; }
  .body { padding: 32px; }
  .message { font-size: 14px; color: #374151; line-height: 1.7; }
  .detail-box { background: #fffbeb; border: 1px solid #fde68a; border-left: 3px solid #f59e0b; border-radius: 4px; padding: 16px 20px; margin: 24px 0; }
  .detail-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
  .detail-label { color: #64748b; }
  .detail-value { font-weight: bold; color: #0f172a; }
  .total-row { border-top: 1px solid #fde68a; padding-top: 10px; margin-top: 10px; }
  .total-row .detail-label { font-size: 14px; font-weight: 600; color: #0f172a; }
  .total-row .detail-value { color: #f59e0b; font-size: 16px; }
  .bank-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 14px 20px; margin: 20px 0; font-size: 12px; color: #475569; line-height: 1.6; }
  .note { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #15803d; }
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
      Dear {{ $subscription->client->name ?? 'Customer' }},<br><br>
      Your subscription <strong>{{ $subscription->name }}</strong> renews in
      <strong>{{ $days }} {{ \Illuminate\Support\Str::plural('day', $days) }}</strong>.
    </div>
    <div class="detail-box">
      <div class="detail-row">
        <span class="detail-label">Subscription</span>
        <span class="detail-value">{{ $subscription->subscription_number }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Billing Cycle</span>
        <span class="detail-value">{{ $subscription->billing_cycle_label }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Next Billing Date</span>
        <span class="detail-value">{{ $subscription->next_billing_date->format('M d, Y') }}</span>
      </div>
      <div class="detail-row total-row">
        <span class="detail-label">Amount Due</span>
        <span class="detail-value">{{ $company->currency ?: 'LKR' }} {{ number_format((float)$subscription->amount, 2) }}</span>
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

    <div class="note">
      No action needed — your invoice will be sent automatically on the billing date.
    </div>

    <div class="message" style="font-size:13px; color:#64748b;">
      If you'd like to pause or cancel this subscription, please reply to this email or contact us
      @if($company->phone) at {{ $company->phone }} @endif.
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
