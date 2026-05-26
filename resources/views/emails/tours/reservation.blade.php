{{--
    Phase 2 minimal Mailable.  Phase 5 will replace this with a
    Markdown / themed template that uses the tenant's brand tokens.

    Vars (injected by BookingReservationMail::content()):
      $booking      Booking model
      $paymentUrl   string|null   — payment-resume URL
      $expiresAt    Carbon|null   — hold_expires_at
      $totalAmount  float         — total in major units
      $currency     string        — ISO-4217 code
--}}
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Rezervasyon Alındı</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 560px; margin: 24px auto; color: #1f2937;">

<h2 style="margin-bottom: 4px;">Rezervasyonunuzu Aldık</h2>
<p style="color: #6b7280; margin-top: 0;">Referans: <strong>{{ $booking->booking_ref }}</strong></p>

<p>Merhaba {{ $booking->customer_snapshot['full_name'] ?? '' }},</p>

<p>
    Rezervasyonunuz başarıyla oluşturuldu ve <strong>{{ number_format($totalAmount, 2, ',', '.') }} {{ $currency }}</strong>
    tutarındaki ödemeniz beklenmektedir.
</p>

@if($expiresAt)
<p style="background: #fef3c7; padding: 12px 16px; border-radius: 8px; border: 1px solid #fcd34d;">
    ⏰ Rezervasyon süreniz <strong>{{ $expiresAt->isoFormat('D MMMM, HH:mm') }}</strong>'da dolacak.
    Bu süre içinde ödemenizi tamamlamazsanız koltuğunuz başka bir misafire tahsis edilebilir.
</p>
@endif

@if($paymentUrl)
<p style="text-align: center; margin: 28px 0;">
    <a href="{{ $paymentUrl }}"
       style="display: inline-block; padding: 12px 28px; background: #4338ca; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
        Ödemeyi Tamamla
    </a>
</p>
@endif

<p style="font-size: 13px; color: #6b7280; margin-top: 32px;">
    Sorularınız için bu e-postayı yanıtlamanız yeterli — operatörlerimiz size ulaşacaktır.
</p>

</body>
</html>
