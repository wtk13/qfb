<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .cta-button { display: inline-block; background-color: #4F46E5; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        @if($logoPath)
            <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $businessName }}" style="max-height: 60px;">
        @endif
    </div>

    <h2>{{ __('campaign.email_heading', ['business' => $businessName]) }}</h2>
    <p>{{ __('campaign.email_body', ['business' => $businessName]) }}</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $ratingUrl }}" class="cta-button">{{ __('campaign.email_cta') }}</a>
    </p>

    <p>{{ __('campaign.email_thanks') }}</p>

    <div class="footer">
        <p>{{ __('campaign.email_footer') }}</p>
    </div>
</body>
</html>
