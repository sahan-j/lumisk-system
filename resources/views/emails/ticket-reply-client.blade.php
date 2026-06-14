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
  .intro { font-size: 14px; color: #374151; line-height: 1.7; margin: 0 0 20px; }
  .preview { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #6d5cff; border-radius: 6px; padding: 14px 16px; font-size: 13px; line-height: 1.7; color: #374151; white-space: pre-line; }
  .btn { display: inline-block; margin-top: 20px; padding: 11px 26px; border-radius: 6px; background: linear-gradient(135deg, #00d4ff, #6d5cff); color: #fff; text-decoration: none; font-weight: 600; font-size: 14px; }
  .footer { padding: 16px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-logo">{{ $company->name ?? 'Lumisk.' }}</div>
    <div class="header-sub">New reply on ticket {{ $ticket->ticket_number }}</div>
  </div>
  <div class="body">
    <p class="intro">Dear {{ $ticket->client->name }},<br><br>
      Our support team has replied to your ticket <strong>{{ $ticket->ticket_number }}</strong> — {{ $ticket->subject }}.</p>

    @if($messageBody)
      <div class="preview">{{ $messageBody }}</div>
    @endif

    <a href="{{ route('portal.tickets.show', $ticket) }}" class="btn">View &amp; Reply</a>
  </div>
  <div class="footer">
    {{ $company->name }}
    @if($company->email) &nbsp;·&nbsp; {{ $company->email }} @endif
    @if($company->phone) &nbsp;·&nbsp; {{ $company->phone }} @endif
  </div>
</div>
</body>
</html>
