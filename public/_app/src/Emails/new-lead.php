<?php
/**
 * @var array $lead
 * @var string $business_name
 * @var string $business_phone
 */
$row = static function (string $label, string $value): string {
    return '<tr>
        <td style="padding:8px 12px;color:#555;font-weight:600;width:120px;border-bottom:1px solid #eee;">' . htmlspecialchars($label) . '</td>
        <td style="padding:8px 12px;border-bottom:1px solid #eee;">' . htmlspecialchars($value) . '</td>
    </tr>';
};
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>New Lead</title></head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#222;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center" style="padding:24px;">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                <tr>
                    <td style="background:#298acc;color:#fff;padding:18px 24px;">
                        <h2 style="margin:0;font-size:18px;">🎉 New Lead Received</h2>
                        <div style="opacity:0.9;font-size:13px;margin-top:4px;"><?php echo htmlspecialchars($business_name) ?> — Website Enquiry</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px;">
                        <p style="margin-top:0;">A new lead has been captured from the website. Details below:</p>
                        <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eee;border-radius:6px;border-collapse:collapse;">
                            <?= $row('Lead ID', '#' . (int) ($lead['id'] ?? 0)) ?>
                            <?= $row('Name', $lead['name'] ?? '') ?>
                            <?= $row('Email', $lead['email'] ?? '') ?>
                            <?= $row('Phone', $lead['phone'] ?? '') ?>
                            <?= $row('City', $lead['city'] ?? '') ?>
                            <?= $row('Destination', $lead['destination'] ?? '') ?>
                            <?php if (!empty($lead['message'])): ?>
                                <?php echo $row('Message', nl2br(htmlspecialchars($lead['message']))) ?>
                            <?php endif; ?>
                            <?= $row('IP', $lead['ip_address'] ?? '') ?>
                            <?php echo $row('Time', date('Y-m-d H:i:s')) ?>
                        </table>

                        <p style="margin-top:24px;">
                            <strong>Recommended next steps:</strong>
                        </p>
                        <ol style="padding-left:20px;color:#444;line-height:1.6;">
                            <li>Call the customer within 5-15 minutes.</li>
                            <li>Prepare 2-3 customised package options.</li>
                            <li>Email a formal quote with inclusions.</li>
                            <li>Follow up via WhatsApp for faster conversion.</li>
                        </ol>

                        <div style="margin-top:24px;padding:14px;background:#f8f9fb;border-radius:6px;font-size:13px;color:#555;">
                            📞 <strong>Business phone:</strong> <?php echo htmlspecialchars($business_phone) ?><br>
                            📊 Saved to <code>leads.sqlite</code> — Lead #<?= (int) ($lead['id'] ?? 0) ?>
                        </div>
                    </td>
                </tr>
            </table>
            <div style="color:#999;font-size:11px;margin-top:16px;">
                This is an automated notification from <?php echo htmlspecialchars($business_name) ?> website.
            </div>
        </td>
    </tr>
</table>
</body>
</html>