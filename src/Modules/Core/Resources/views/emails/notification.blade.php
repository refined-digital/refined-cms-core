@php
    // fully-inlined for email-client compatibility. branding comes in via the
    // Mailable; neutral fallbacks keep any non-form-builder caller working.
    $accent   = $accent ?? '#1f2937';
    $siteName = $siteName ?? config('app.name');
    $siteUrl  = $siteUrl ?? config('app.url');
    $logo     = $logo ?? null;
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ $heading ?? $siteName }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; -webkit-font-smoothing:antialiased;">
    <span style="display:none; font-size:0; line-height:0; max-height:0; overflow:hidden; opacity:0;">{{ $heading ?? ('New submission for '.$siteName) }}</span>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08); font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

                    {{-- accent bar --}}
                    <tr>
                        <td style="height:4px; line-height:4px; font-size:0; background-color:{{ $accent }};">&nbsp;</td>
                    </tr>

                    {{-- header: the site logo, or its name as a wordmark fallback.
                         (the subject lives in the email subject line, not the body.) --}}
                    <tr>
                        <td style="padding:32px 40px 0 40px;">
                            @if ($logo)
                                <a href="{{ $siteUrl }}" style="text-decoration:none;">
                                    <img src="{{ $logo }}" alt="{{ $siteName }}" height="40" style="display:block; height:40px; border:0;">
                                </a>
                            @else
                                <a href="{{ $siteUrl }}" style="text-decoration:none; color:{{ $accent }}; font-size:18px; font-weight:700; letter-spacing:-0.01em;">{{ $siteName }}</a>
                            @endif
                        </td>
                    </tr>

                    {{-- body (message copy + the [[fields]] block, already inlined) --}}
                    <tr>
                        <td style="padding:20px 40px 8px 40px; font-size:15px; line-height:1.6; color:#374151;">
                            {!! $body !!}
                        </td>
                    </tr>

                    {{-- footer --}}
                    <tr>
                        <td style="padding:24px 40px 32px 40px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr><td style="border-top:1px solid #e5e7eb; height:1px; line-height:1px; font-size:0;">&nbsp;</td></tr>
                            </table>
                            <p style="margin:16px 0 0 0; font-size:12px; line-height:1.5; color:#9ca3af;">
                                This message was sent from <a href="{{ $siteUrl }}" style="color:#9ca3af;">{{ $siteName }}</a>.<br>
                                &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
