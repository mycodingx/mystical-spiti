<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Mystical Expedition</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f7f9fc;color:#1a2332;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px}
        .wrap{max-width:480px;width:100%}
        .code{font-size:7rem;font-weight:800;color:#298acc;line-height:1;margin-bottom:16px}
        h1{font-size:1.8rem;margin-bottom:12px}
        p{color:#6b7a8d;margin-bottom:28px;line-height:1.6}
        .btn{display:inline-flex;align-items:center;gap:8px;background:#298acc;color:#fff;padding:12px 28px;border-radius:100px;text-decoration:none;font-weight:600;font-size:1rem;transition:background .2s}
        .btn:hover{background:#1e6fa0}
        .btn-wa{background:#25d366;margin-left:10px}
        .btn-wa:hover{background:#1aad55}
        .actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you&rsquo;re looking for doesn&rsquo;t exist or has been moved.<br>Let us help you find the right Himachal package!</p>
        <div class="actions">
            <a href="/" class="btn"><i class="fa-solid fa-house"></i> Back to Home</a>
            <a href="https://wa.me/918219000937?text=Hi%2C%20I%20need%20help%20finding%20a%20package" target="_blank" rel="noopener" class="btn btn-wa"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
        </div>
    </div>
</body>
</html>