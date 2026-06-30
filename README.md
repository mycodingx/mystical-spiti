# Mystical Expedition - Landing Page

> Himachal Tour Packages landing page with lead capture, secure form handling,
> and a modern Bootstrap 5 / PHP 8 architecture.

**Live**: TBD (deploy to subdomain)
**Stack**: PHP 8.1+, Bootstrap 5.3, SQLite, PHPMailer 6.9, vanilla JS
**License**: Proprietary

---

## Features

- ✅ **Lead capture** — 4 conversion surfaces (hero form, sticky mobile CTA, exit-intent modal, per-package button)
- ✅ **Secure forms** — CSRF tokens, honeypot, rate-limiting (5/hr per IP), server-side validation
- ✅ **SQLite + Email** — every lead is persisted AND emailed to your inbox
- ✅ **JSON-driven content** — packages & reviews in `data/*.json`, easy to edit
- ✅ **Mobile-first responsive** — tested from 320px to 1440px
- ✅ **SEO-ready** — JSON-LD `TravelAgency`, OG tags, sitemap, robots.txt
- ✅ **Performance** — lazy-loaded images, gzipped CSS, browser cache rules
- ✅ **Security** — security headers (CSP, X-Frame-Options), HTTPS-ready, vendor/ & .env protected by .htaccess
- ✅ **Accessibility** — labels, focus management, modal traps, reduced-motion support
- ✅ **Analytics-ready** — GTM/GA4 dataLayer push on lead submit

---

## Quick Start (Local / XAMPP)

```powershell
# 1. Clone or copy files into your XAMPP htdocs
cd C:\xampp\htdocs\mystical

# 2. Install PHP dependencies
composer install

# 3. Create local .env (copy from template)
copy .env.example .env
# Edit .env and fill in your real SMTP password

# 4. Make sure .env DB_PATH points to a writable location
# Default: data/leads.sqlite (auto-created on first run)

# 5. Start Apache (configured on port 8080) + browse to:
#    http://localhost:8080/mystical/public/
```

That's it — no database server, no Node, no build step.

---

## Project Structure

```
mystical/
├── .env.example                Template for SMTP creds
├── .gitignore
├── .htaccess                   Root safety deny rules
├── composer.json               Dependency manifest
├── composer.lock
├── schema.sql                  SQLite DDL (auto-run on first hit)
├── PLAN.md                     Original refactor plan
├── README.md                   This file
│
├── public/                     ★ APACHE DOCUMENT ROOT
│   ├── .htaccess               Security headers, caching, HTTPS
│   ├── index.php               Landing page
│   ├── thanks.php              Thank-you page
│   ├── submit.php              AJAX form endpoint
│   ├── 404.php / 500.php       Error pages
│   ├── robots.txt
│   └── sitemap.xml
│
├── src/                        PHP classes (NOT web-accessible)
│   ├── Bootstrap.php           Autoload, env, error handler, session
│   ├── Database.php            SQLite singleton + migrations
│   ├── Csrf.php                CSRF tokens (timing-safe)
│   ├── LeadService.php         Validate + sanitise + persist
│   ├── MailService.php         PHPMailer wrapper
│   └── Emails/                 HTML email templates
│       ├── new-lead.php
│       └── user-confirmation.php
│
├── views/                      HTML partials (server-rendered)
│   ├── partials/
│   │   ├── header.php          <head>, top bar, hero, form
│   │   ├── footer.php          Footer + modals + scripts
│   │   ├── package-card.php    Looped per package
│   │   ├── review-card.php     Looped per review
│   │   └── enquiry-modal.php   (in footer.php)
│   └── pages/
│       ├── home.php
│       └── thanks.php
│
├── data/
│   ├── packages.json           12 tour packages (edit me!)
│   ├── reviews.json            6 testimonials
│   └── leads.sqlite            (auto-created, gitignored)
│
├── assets/
│   ├── css/
│   │   ├── variables.css       Design tokens (colors, spacing, fonts)
│   │   └── main.css            All styles (BEM-ish, responsive built-in)
│   ├── js/
│   │   ├── main.js             Modal, AJAX, exit-intent, smooth scroll
│   │   └── validation.js       Client-side form validation
│   └── img/
│       ├── brand/              Logo, favicon, guarantee, Himachal Tourism
│       ├── himachal/           Destination photos
│       ├── icons/              UI icons (flight, hotel, etc.)
│       └── payments/           Visa, MC, PayPal, AmEx
│
├── vendor/                     Composer (gitignored)
└── logs/                       error.log (gitignored)
```

---

## Editing Content

### Add / edit a tour package

Edit `data/packages.json` — each package follows this schema:

```json
{
  "slug": "shimla",
  "title": "Shimla Tour Package",
  "image": "shimla.jpg",
  "duration": "3N / 4D",
  "price": "On Request",
  "destinations": ["Shimla", "Kufri", "Chail", "Mall Road"],
  "inclusions": ["Flight", "Hotel", "Sightseeing", "Meals", "Transfers"],
  "itinerary_short": [
    { "day": 1, "title": "Arrival in Shimla", "desc": "Hotel check-in..." },
    { "day": 2, "title": "Sightseeing", "desc": "..." }
  ],
  "itinerary_more": [{ "day": 3, "title": "...", "desc": "..." }]
}
```

The `title` is used by the form's destination dropdown — keep them consistent.

### Add / edit a testimonial

Edit `data/reviews.json`:

```json
{
  "name": "Aditi Sharma",
  "location": "Delhi, India",
  "rating": 5,
  "text": "..."
}
```

### Change brand colors / fonts

Edit `assets/css/variables.css`. Every color/spacing/font used across the site
flows from the CSS custom properties here.

### Change SMTP / business contact

Edit `.env` (or `.env.example` for documentation). Never commit `.env`.

---

## Deployment (Hostinger Subdomain)

1. **Create subdomain** in Hostinger control panel
   - e.g. `book.mysticalexpedition.com`
2. **Point DocumentRoot to `public/`**
   - In Hostinger → Domains → Subdomain → Document Root
   - Set to `/home/user/domains/book.mysticalexpedition.com/public_html/public` (adjust path)
3. **Upload files** via FTP/SFTP or Git
4. **SSH into the server** (Hostinger has SSH access)
5. **Install Composer dependencies** (production):
   ```bash
   cd /home/user/domains/book.mysticalexpedition.com/public_html
   composer install --no-dev --optimize-autoloader
   ```
6. **Create `.env`** on the server with real SMTP password
   ```bash
   cp .env.example .env
   nano .env
   chmod 600 .env
   ```
7. **Enable SSL** — Hostinger free Let's Encrypt
8. **Force HTTPS** — uncomment the HTTPS redirect block in `public/.htaccess`
9. **Test**:
   - Submit the form
   - Check your inbox for the lead notification
   - Browse to `data/leads.sqlite` (via cPanel File Manager) — you should see the new row
10. **Submit sitemap** to Google Search Console at `/sitemap.xml`

---

## Custom Domain Instead of Subdomain?

If you want `mysticalexpedition.com` itself to serve the app:

1. Set DocumentRoot of the main domain to `public/`
2. Keep `index.html` redirect at the root if you need to, or move static landing here

---

## Verifying Locally

### Smoke test (no DB needed)

```powershell
php scripts/smoke.php
```

Runs 6 end-to-end checks:

1. Bootstrap loads
2. CSRF tokens generate
3. Database migrates + connects
4. Lead service validates & inserts
5. Validation rejects bad input
6. Honeypot blocks bots

### Render test (verify templates)

```powershell
php scripts/render-test.php
```

Confirms the home page renders with all 12 packages, 6 reviews, forms, modals.

### PHP lint

```powershell
powershell -ExecutionPolicy Bypass -File lint.ps1
```

### Check submitted leads

```powershell
# Quick CLI query
php -r "require 'vendor/autoload.php'; foreach (['APP_DEBUG'=>'true','DB_PATH'=>'data/leads.sqlite'] as \$k=>\$v) \$_ENV[\$k]=\$v; \$pdo = Mystical\Database::getInstance(); foreach (\$pdo->query('SELECT id,name,phone,destination,status,created_at FROM leads ORDER BY id DESC LIMIT 10') as \$row) { echo \$row['id'].' | '.\$row['name'].' | '.\$row['phone'].' | '.\$row['destination'].' | '.\$row['status'].' | '.\$row['created_at'].PHP_EOL; }"
```

---

## Security Notes

| Layer      | Protection                                                                       |
| ---------- | -------------------------------------------------------------------------------- |
| SMTP creds | Stored only in `.env` (gitignored) on the server                                 |
| Forms      | CSRF tokens (rotated after use), timing-safe compare                             |
| Spam       | Honeypot field + IP rate-limit (5/hour)                                          |
| Input      | Whitelist validation (name regex, Indian mobile, email filter)                   |
| Headers    | CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy                    |
| Files      | `.htaccess` denies access to `.env`, `vendor/`, `src/`, `data/*.sqlite`, `logs/` |
| Errors     | Logged to `logs/error.log`, hidden from browser output                           |
| Sessions   | HttpOnly cookies, SameSite=Lax, Secure flag in production                        |

---

## Performance

- **CSS**: ~30 KB, gzipped to ~6 KB
- **JS**: ~10 KB combined, deferred loading
- **Images**: lazy-loaded, `decoding="async"`, responsive sizing
- **Caching**: 1y for CSS/JS, 30d for images, 0s for HTML (via `.htaccess`)
- **Compression**: gzip enabled (`mod_deflate`)

---

## Browser Support

- Chrome / Edge (last 2 versions)
- Firefox (last 2 versions)
- Safari 14+ (iOS 14+)
- Graceful degradation for older browsers

---

## Troubleshooting

**"Class 'Dotenv\Dotenv' not found"** — run `composer install`
**"Could not read database"** — check `DB_PATH` in `.env` and folder permissions
**"Mailer Error: SMTP connect() failed"** — check `MAIL_HOST`, `MAIL_PORT`, `MAIL_PASSWORD` in `.env`
**Submit works but no email arrives** — check spam folder; verify SPF/DKIM for your domain in Hostinger
**Modal won't close** — press `Esc`, click the backdrop, or click the × button

---

## License

Proprietary — © Mystical Expedition. All rights reserved.
