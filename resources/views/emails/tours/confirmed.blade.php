{{--
    Phase 2 minimal Mailable.  Phase 5 / 3 will attach the voucher PDF
    via spatie/laravel-pdf and re-skin with the tenant's brand tokens.

    Vars (injected by BookingConfirmedMail::content()):
      $booking    Booking model
      $totalPaid  float
      $currency   string
--}}
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Rezervasyon Onaylandı</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 560px; margin: 24px auto; color: #1f2937;">

<h2 style="margin-bottom: 4px;">Rezervasyonunuz Onaylandı 🎉</h2>
<p style="color: #6b7280; margin-top: 0;">Referans: <strong>{{ $booking->booking_ref }}</strong></p>

<p>Merhaba {{ $booking->customer_snapshot['full_name'] ?? '' }},</p>

<p>
    Ödemeniz başarıyla alındı (<strong>{{ number_format($totalPaid, 2, ',', '.') }} {{ $currency }}</strong>).
    Rezervasyonunuz onaylanmıştır.
</p>

@if($booking->tourDate?->starts_at)
<p style="background: #dcfce7; padding: 12px 16px; border-radius: 8px; border: 1px solid #86efac;">
    📅 Hareket tarihi: <strong>{{ $booking->tourDate->starts_at->isoFormat('D MMMM YYYY, HH:mm') }}</strong>
</p>
@endif

<p>
    Voucher belgesi en geç birkaç dakika içinde ayrı bir e-posta ile gönderilecektir.
    Hareket gününde voucher'ınızdaki QR kodu operatörümüze gösterin.
</p>

<p style="font-size: 13px; color: #6b7280; margin-top: 32px;">
    İyi yolculuklar dileriz!
</p>

</body>
</html>
