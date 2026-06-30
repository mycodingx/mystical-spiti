<?php
/**
 * @var array $lead
 * @var string $business_name
 * @var string $business_phone
 * @var string $business_address
 */
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Thank you</title></head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#222;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center" style="padding:24px;">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                <tr>
                    <td style="background:#298acc;color:#fff;padding:24px;text-align:center;">
                        <div style="font-size:42px;line-height:1;">✓</div>
                        <h2 style="margin:8px 0 0;font-size:22px;">Thank you, <?php echo htmlspecialchars(explode(' ', $lead['name'])[0]) ?>!</h2>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;color:#333;line-height:1.6;">
                        <p style="margin-top:0;">We've received your enquiry about <strong><?php echo htmlspecialchars($lead['destination']) ?></strong>.</p>
                        <p>One of our travel experts will get in touch with you shortly with a customised itinerary and the best prices for your trip.</p>

                        <div style="margin:24px 0;padding:16px;background:#f8f9fb;border-radius:6px;border-left:4px solid #298acc;">
                            <strong>What happens next?</strong>
                            <ul style="margin:8px 0 0;padding-left:20px;">
                                <li>Our team is reviewing your request right now</li>
                                <li>You'll receive a call within 5–15 minutes</li>
                                <li>We'll share 2–3 customised <?php echo htmlspecialchars($lead['destination']) ?> packages</li>
                            </ul>
                        </div>

                        <p style="margin-bottom:6px;">Need to talk sooner? Reach us directly:</p>
                        <p style="margin:4px 0;">📞 <strong><?php echo htmlspecialchars($business_phone) ?></strong></p>
                        <p style="margin:4px 0;">✉️ <a href="mailto:<?php echo htmlspecialchars(Bootstrap::config('BUSINESS_EMAIL')) ?>" style="color:#298acc;"><?php echo htmlspecialchars(Bootstrap::config('BUSINESS_EMAIL')) ?></a></p>

                        <p style="margin-top:28px;">Warm regards,<br><strong><?php echo htmlspecialchars($business_name) ?></strong></p>
                        <p style="color:#888;font-size:12px;margin-top:16px;"><?php echo htmlspecialchars($business_address) ?></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>