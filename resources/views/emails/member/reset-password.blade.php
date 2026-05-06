<!DOCTYPE html>
<html lang="tr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Şifre Sıfırlama — {{ $siteName }}</title>
    <style>
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }

        /* Base */
        body { background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; background-color: #f3f4f6; padding: 32px 16px; box-sizing: border-box; }
        .container { max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 8px rgba(0,0,0,.06); }

        /* Header */
        .header { background-color: {{ $primaryColor }}; padding: 28px 32px; text-align: center; }
        .header img { max-height: 48px; max-width: 200px; }
        .header-text { color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.01em; }

        /* Body */
        .body { padding: 32px; }
        .greeting { font-size: 17px; font-weight: 600; color: #111827; margin: 0 0 12px; }
        .text { font-size: 15px; color: #374151; line-height: 1.65; margin: 0 0 20px; }
        .text-sm { font-size: 13px; color: #6b7280; line-height: 1.6; margin: 0 0 12px; }

        /* CTA Button */
        .btn-wrap { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; padding: 14px 32px; background-color: {{ $primaryColor }}; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: 600; letter-spacing: 0.01em; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }

        /* URL fallback */
        .url-fallback { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; word-break: break-all; }
        .url-fallback a { color: {{ $primaryColor }}; font-size: 13px; text-decoration: none; }

        /* Footer */
        .footer { background-color: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 32px; text-align: center; }
        .footer-text { font-size: 12px; color: #9ca3af; line-height: 1.6; margin: 0; }
        .footer-text a { color: #9ca3af; text-decoration: underline; }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .body { padding: 24px 20px !important; }
            .footer { padding: 16px 20px !important; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0">

        {{-- ── Header ────────────────────────────────────────────────────── --}}
        <tr>
            <td class="header">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}">
                @else
                    <div class="header-text">{{ $siteName }}</div>
                @endif
            </td>
        </tr>

        {{-- ── Body ─────────────────────────────────────────────────────── --}}
        <tr>
            <td class="body">

                <p class="greeting">Merhaba{{ $memberName ? ', ' . e($memberName) : '' }} 👋</p>

                <p class="text">
                    <strong>{{ $siteName }}</strong> hesabınız için şifre sıfırlama talebinde bulundunuz.
                    Aşağıdaki butona tıklayarak yeni şifrenizi belirleyebilirsiniz.
                </p>

                {{-- CTA --}}
                <div class="btn-wrap">
                    <a href="{{ $resetUrl }}" class="btn" target="_blank">
                        🔑 Şifremi Sıfırla
                    </a>
                </div>

                <hr class="divider">

                <p class="text-sm">
                    Bu bağlantı <strong>{{ $expireMinutes }} dakika</strong> içinde geçerliliğini yitirecektir.
                    Süre dolduktan sonra yeni bir sıfırlama talebinde bulunmanız gerekir.
                </p>

                <p class="text-sm">
                    Eğer bu talebi siz oluşturmadıysanız bu e-postayı dikkate almayınız.
                    Hesabınız güvende, herhangi bir değişiklik yapılmamıştır.
                </p>

                {{-- URL fallback for email clients that block buttons --}}
                <p class="text-sm" style="margin-top:20px;">
                    Butona tıklayamıyorsanız aşağıdaki bağlantıyı tarayıcınıza kopyalayın:
                </p>
                <div class="url-fallback">
                    <a href="{{ $resetUrl }}" target="_blank">{{ $resetUrl }}</a>
                </div>

            </td>
        </tr>

        {{-- ── Footer ───────────────────────────────────────────────────── --}}
        <tr>
            <td class="footer">
                <p class="footer-text">
                    Bu e-posta <a href="{{ $siteUrl }}" target="_blank">{{ $siteName }}</a> tarafından gönderilmiştir.<br>
                    Üye hesabınızla ilgili sorularınız için web sitemizi ziyaret edebilirsiniz.
                </p>
            </td>
        </tr>

    </table>
</div>
</body>
</html>
