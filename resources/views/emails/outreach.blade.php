<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.7; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

{!! nl2br(e($emailBody)) !!}

<div style="margin-top: 40px; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 15px;">
    <p>{{ $senderName }} | QuickFeedback | Warsaw, Poland</p>
    <p><a href="{{ $unsubscribeUrl }}" style="color: #999;">Unsubscribe</a> — you'll be removed immediately.</p>
</div>
</body>
</html>
