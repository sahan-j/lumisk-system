<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #0f172a; background: #f8fafc; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
  .header { background: linear-gradient(135deg, #00d4ff, #6d5cff); padding: 24px 32px; }
  .header-logo { font-size: 20px; font-weight: bold; color: #fff; letter-spacing: -0.5px; }
  .header-sub { font-size: 13px; color: rgba(255,255,255,0.85); margin-top: 4px; }
  .body { padding: 32px; }
  .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #6d5cff; border-radius: 4px; padding: 16px 20px; margin: 0 0 20px; }
  .detail-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
  .detail-label { color: #64748b; }
  .detail-value { font-weight: bold; color: #0f172a; }
  .preview { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; font-size: 13px; line-height: 1.7; color: #374151; white-space: pre-line; }
  .btn { display: inline-block; margin-top: 20px; padding: 11px 26px; border-radius: 6px; background: linear-gradient(135deg, #00d4ff, #6d5cff); color: #fff; text-decoration: none; font-weight: 600; font-size: 14px; }
  .footer { padding: 16px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-logo">{{ $company->name ?? 'Lumisk.' }}</div>
    <div class="header-sub">Client replied on {{ $ticket->ticket_number }}</div>
  </div>
  <div class="body">
    <div class="detail-box">
      <div class="detail-row"><span class="detail-label">Ticket</span><span class="detail-value">{{ $ticket->ticket_number }}</span></div>
      <div class="detail-row"><span class="detail-label">Subject</span><span class="detail-value">{{ $ticket->subject }}</span></div>
      <div class="detail-row"><span class="detail-label">Client</span><span class="detail-value">{{ $ticket->client->name }}</span></div>
    </div>

    @if($messageBody)
      <p style="font-size:12px; color:#94a3b8; margin:0 0 6px;">Reply</p>
      <div class="preview">{{ $messageBody }}</div>
    @endif

    <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn">View Ticket</a>
  </div>
  <div class="footer">
    {{ $company->name }}
    @if($company->email) &nbsp;·&nbsp; {{ $company->email }} @endif
  </div>
</div>
</body>
</html>
