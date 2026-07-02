<?php http_response_code(500); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | Mystical Expedition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Poppins:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: #0a1422;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
        }
        .wrap { max-width: 520px; width: 100%; }

        .mountain { margin-bottom: 24px; opacity: 0.18; }

        .code {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 7.5rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 4px;
        }
        .label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 100px;
            margin-bottom: 20px;
        }
        h1 {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 1.7rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        p { color: rgba(255,255,255,0.55); margin-bottom: 32px; line-height: 1.7; font-size: 15px; }
        p strong { color: rgba(255,255,255,0.8); }

        .card {
            background: #121e30;
            border: 1px solid rgba(255,255,255,0.07);
            border-top: 3px solid #ef4444;
            border-radius: 18px;
            padding: 48px 40px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        }

        .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 24px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 26px; border-radius: 100px;
            text-decoration: none; font-weight: 600; font-size: 15px;
            transition: all .2s ease;
        }
        .btn-home {
            background: linear-gradient(135deg, #c9531a, #e8a020);
            color: #fff;
            box-shadow: 0 4px 14px rgba(201,83,26,0.4);
        }
        .btn-home:hover { box-shadow: 0 6px 20px rgba(201,83,26,0.6); transform: translateY(-2px); }
        .btn-wa { background: #25d366; color: #fff; box-shadow: 0 4px 12px rgba(37,211,102,0.3); }
        .btn-wa:hover { background: #1aad55; transform: translateY(-2px); }

        .back { font-size: 13px; color: rgba(255,255,255,0.3); text-decoration: none; transition: color .2s; }
        .back:hover { color: #c9531a; }
        .back i { margin-right: 4px; }

        @media (max-width: 480px) {
            .card { padding: 32px 20px; }
            .code { font-size: 5rem; }
            h1 { font-size: 1.3rem; }
            .actions { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <svg class="mountain" viewBox="0 0 600 80" width="200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <polygon points="0,80 120,10 200,45 300,0 400,35 480,15 600,80" fill="#ef4444"/>
            </svg>
            <div class="code">500</div>
            <div class="label"><i class="fa-solid fa-triangle-exclamation"></i> Server Error</div>
            <h1>A Storm Hit Our Servers</h1>
            <p>Something went wrong on our end — like an unexpected blizzard on the Kunzum Pass.<br>
               Please <strong>try again in a moment</strong> or reach us directly on WhatsApp.</p>
            <div class="actions">
                <a href="/" class="btn btn-home"><i class="fa-solid fa-house"></i> Back to Home</a>
                <a href="https://wa.me/918894042702?text=Hi%2C%20I%20got%20a%20500%20error%20on%20your%20website" target="_blank" rel="noopener" class="btn btn-wa">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp Us
                </a>
            </div>
            <a href="javascript:history.back()" class="back"><i class="fa-solid fa-arrow-left"></i> Go back</a>
        </div>
    </div>
</body>
</html>