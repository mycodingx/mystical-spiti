# Mystical Expedition — Landing Page Upgrade Plan

**Project**: Himachal Tour Packages Lead-Capture Landing Page
**Business**: Mystical Expedition (Shoghi, Shimla, Himachal Pradesh)
**Goal**: Convert a static, copy-pasted landing page into a modern, secure, lead-optimized PHP landing page
**Date**: 2026-06-29
**Status**: 📋 Planning — pending approval

---

## 1. Current State Audit

### 1.1 Existing Files

```
mystical/
├── index.html              2,500+ lines, static, duplicated package cards
├── mail.php                SMTP creds hardcoded, insecure, legacy PHPMailer
├── thanks.html             Static thank-you page
├── css/
│   ├── style1.css          516 lines, monolithic
│   └── responsive.css      67 lines, separate concerns
├── Himachal/               Contains only kinnaur.webp (orphaned)
├── images/
│   ├── himachal/           23 destination photos
│   └── icons/              28 UI icons
└── PHPMailer/              Legacy PHP 5 autoload, outdated
```

### 1.2 Critical Issues Found

| #   | Area               | Issue                                                                        | Severity    |
| --- | ------------------ | ---------------------------------------------------------------------------- | ----------- |
| 1   | `mail.php`         | **SMTP password committed to repo**                                          | 🔴 Critical |
| 2   | `mail.php`         | `display_errors = 1` + `error_reporting(E_ALL)` exposes errors in production | 🔴 Critical |
| 3   | `mail.php`         | No CSRF protection, no input validation, no spam protection                  | 🔴 Critical |
| 4   | `index.html`       | Bootstrap CSS loaded twice (5.3.2 + 5.3.0)                                   | 🟠 High     |
| 5   | `index.html`       | 12 copy-pasted package cards (~180 lines each)                               | 🟠 High     |
| 6   | `index.html`       | Inline `<style>` blocks scattered through markup                             | 🟠 High     |
| 7   | `index.html`       | Auto-popup modal on every page load — hurts UX & conversion                  | 🟠 High     |
| 8   | Floating WhatsApp  | URL has leading space (`+91- 8219000937`) — broken on some clients           | 🟠 High     |
| 9   | No `.htaccess`     | No security headers, no caching, no HTTPS enforcement                        | 🟠 High     |
| 10  | No `composer.json` | No dependency management, no autoloading                                     | 🟠 High     |
| 11  | No git repo        | No version control — changes can't be tracked or rolled back                 | 🟠 High     |
| 12  | No SEO             | No JSON-LD, no OG tags, no sitemap, no Schema.org markup                     | 🟡 Medium   |
| 13  | CSS                | No variables, no BEM naming, no minified build                               | 🟡 Medium   |
| 14  | Images             | No `srcset`, no WebP, no `decoding="async"`                                  | 🟡 Medium   |
| 15  | Mobile UX          | No sticky CTA bar, no thumb-friendly tap targets                             | 🟡 Medium   |
| 16  | `Himachal/` folder | Orphaned, contains only one unused file                                      | 🟢 Low      |
| 17  | PHPMailer autoload | Old PHP 5 style — incompatible with modern Composer                          | 🟢 Low      |

### 1.3 Environment

- **OS**: Windows 10/11
- **PHP**: 8.2.12 (XAMPP)
- **Composer**: 2.9.5 ✅
- **Web server**: Apache (XAMPP)
- **Production**: Hostinger (subdomain deployment)
- **SMTP**: smtp.hostinger.com:587 (TLS)

---

## 2. Decisions (Approved)

| Question         | Decision                                        | Rationale                                            |
| ---------------- | ----------------------------------------------- | ---------------------------------------------------- |
| Document root    | **Deploy via subdomain** → `public/` is webroot | Industry best practice; hides config/vendor from URL |
| SMTP credentials | **Strong placeholder** in `.env.example`        | No real password in repo; rotate on Hostinger        |
| Lead storage     | **SQLite database** + email backup              | Queryable, exportable, survives email failures       |
| Version control  | **Init git + baseline commit** first            | Safe rollback point before refactor                  |

---

## 3. Target Architecture

### 3.1 Tech Stack

| Layer              | Choice                         | Why                                                      |
| ------------------ | ------------------------------ | -------------------------------------------------------- |
| Language           | PHP 8.2 (vanilla)              | Already running, no framework overhead                   |
| Dependency manager | Composer 2.x                   | Autoload, version pinning, security audits               |
| Mail               | `phpmailer/phpmailer` ^6.9     | Maintained, Composer-native                              |
| Env management     | `vlucas/phpdotenv` ^5.6        | Industry standard for PHP                                |
| Database           | SQLite (PDO)                   | Zero-config, file-based, perfect for landing page volume |
| CSS framework      | Bootstrap 5.3 via Composer     | Single source of truth, no CDN                           |
| JS                 | Vanilla ES6 + Bootstrap bundle | No jQuery dependency                                     |
| Web server         | Apache + `.htaccess`           | Already on XAMPP & Hostinger                             |

### 3.2 Final Folder Structure

```
mystical/                                  ← Git repo root
│
├── .env                                    ← Real SMTP creds (gitignored)
├── .env.example                            ← Safe template for team
├── .gitignore                              ← vendor, .env, logs, .sqlite
├── .htaccess                               ← Root deny (safety net for subdomain)
├── composer.json                           ← Dependency manifest
├── composer.lock                           ← Pinned versions
├── README.md                               ← Setup + deploy guide
├── PLAN.md                                 ← This file
├── schema.sql                              ← SQLite DDL for leads table
│
├── public/                                 ← APACHE DOCUMENT ROOT (subdomain)
│   ├── .htaccess                           ← Security + cache + HTTPS
│   ├── index.php                           ← Landing page (was index.html)
│   ├── thanks.php                          ← Thank-you page (was thanks.html)
│   ├── submit.php                          ← AJAX form endpoint
│   ├── robots.txt
│   └── sitemap.xml                         ← Generated
│
├── src/                                    ← PHP classes (NOT web-accessible)
│   ├── Bootstrap.php                       ← Autoload, env, config, error handler
│   ├── Database.php                        ← SQLite PDO singleton + migrations
│   ├── Csrf.php                            ← Token gen/verify (timing-safe)
│   ├── LeadService.php                     ← Validate + sanitize + persist
│   └── MailService.php                     ← PHPMailer factory + HTML template
│
├── views/                                  ← HTML partials
│   ├── partials/
│   │   ├── header.php                      ← <head>, top-bar, nav, schema.org
│   │   ├── footer.php                      ← Footer + scripts
│   │   ├── package-card.php                ← One card → looped over packages.json
│   │   ├── review-card.php                 ← One review → looped over reviews.json
│   │   └── enquiry-modal.php               ← Reusable quote-request modal
│   └── pages/
│       ├── home.php                        ← Composes hero + packages + reviews + about
│       └── thanks.php
│
├── data/                                   ← Static content (JSON) + SQLite
│   ├── packages.json                       ← 12 packages
│   ├── reviews.json                        ← 6 testimonials
│   └── leads.sqlite                        ← Created on first run (gitignored)
│
├── assets/
│   ├── css/
│   │   ├── variables.css                   ← CSS custom properties (colors, spacing)
│   │   └── main.css                        ← Merged style1 + responsive (BEM-ish)
│   ├── js/
│   │   ├── main.js                         ← Read-more, modal, exit-intent, sticky CTA
│   │   └── validation.js                   ← Client-side form validation
│   └── img/
│       ├── brand/                          ← logo, favicon (from images/)
│       ├── himachal/                       ← destination photos (from images/himachal/)
│       ├── icons/                          ← UI icons (from images/icons/)
│       └── payments/                       ← visa, mastercard, paypal (from images/icons/)
│
├── vendor/                                 ← Composer dependencies (gitignored)
└── logs/                                   ← Error + audit logs (gitignored)
    └── .gitkeep
```

### 3.3 URL Routes

| Method | Path           | File                 | Purpose                       |
| ------ | -------------- | -------------------- | ----------------------------- |
| GET    | `/`            | `public/index.php`   | Landing page with hero form   |
| GET    | `/thanks`      | `public/thanks.php`  | Thank-you confirmation        |
| POST   | `/submit.php`  | `public/submit.php`  | AJAX form submission endpoint |
| GET    | `/robots.txt`  | `public/robots.txt`  | SEO crawler directives        |
| GET    | `/sitemap.xml` | `public/sitemap.xml` | SEO sitemap                   |

> Apache will rewrite `/thanks` → `thanks.php` via `.htaccess` if needed, or keep `.php` extensions for clarity.

---

## 4. Build Phases

### Phase 1 — Foundation (Security & Infrastructure)

**Goal**: Establish version control, dependencies, and web-accessible boundary.

**Tasks**:

1. `git init` in `mystical/`
2. Create `.gitignore` (vendor, .env, logs, data/\*.sqlite, .vscode)
3. First baseline commit (current state as "v1.0-static-legacy")
4. Create `composer.json` with `phpmailer/phpmailer` + `vlucas/phpdotenv`
5. Run `composer install`
6. **Delete** legacy `PHPMailer/` folder (replaced by vendor/)
7. Create `.env.example` with placeholder SMTP creds (strong password format)
8. Create `.env` for local dev (real creds, gitignored)
9. Create `public/.htaccess`:
   - Disable directory listing
   - Block dotfiles
   - Force HTTPS (production only — feature flag)
   - Security headers: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Content-Security-Policy
   - Gzip compression
   - Cache-Control by extension (CSS/JS: 1y, images: 30d, HTML: no-cache)
   - Block access to `.php` files in `assets/` subfolder (via separate `.htaccess`)
10. Create root `.htaccess` as safety net (deny `.env`, `vendor/`, `src/`, `data/*.sqlite`)
11. Move `index.html` → `public/index.php` (rename only; rewrite in Phase 3)
12. Move `thanks.html` → `public/thanks.php`
13. Create `public/robots.txt` (allow all for now)
14. Create `logs/.gitkeep`

**Deliverables**: Git repo, Composer setup, hardened `.htaccess`, file moves complete.

---

### Phase 2 — PHP Backend (Form Processing)

**Goal**: Secure, validated, persisted lead capture with email delivery.

**Tasks**:

1. `src/Bootstrap.php`:
   - Register Composer autoloader
   - Load `.env` via Dotenv
   - Define constants (SMTP_HOST, MAIL_FROM, BUSINESS_NAME, etc.)
   - Set error handler (log to `logs/error.log`, hide in production)
   - Configure session (for CSRF + rate-limit)
2. `src/Database.php`:
   - PDO singleton connecting to `data/leads.sqlite`
   - `migrate()` runs `schema.sql` on first run
   - Helper methods: `insertLead()`, `countRecentByIp()`, `getAllLeads()`
3. `src/Csrf.php`:
   - `generate()` — stores token in session
   - `verify($token)` — uses `hash_equals` for timing-safe compare
4. `src/LeadService.php`:
   - Validate name (2-100 chars, alpha + space)
   - Validate email (`filter_var FILTER_VALIDATE_EMAIL`)
   - Validate phone (10 digits, Indian mobile regex)
   - Validate city (2-50 chars)
   - Validate destination (whitelist from `packages.json`)
   - Honeypot field check (reject if filled)
   - Rate limit (max 5 submissions per IP per hour via session)
   - Persist to SQLite via `Database::insertLead()`
   - Return `LeadResult` object (success/error + user message)
5. `src/MailService.php`:
   - PHPMailer factory with SMTP config from env
   - `sendLeadNotification($lead)` — sends HTML email to business
   - `sendUserConfirmation($lead)` — sends auto-reply to user (optional)
   - HTML email template with inline CSS
6. `schema.sql`:
   ```sql
   CREATE TABLE IF NOT EXISTS leads (
     id INTEGER PRIMARY KEY AUTOINCREMENT,
     name TEXT NOT NULL,
     city TEXT NOT NULL,
     email TEXT NOT NULL,
     phone TEXT NOT NULL,
     destination TEXT NOT NULL,
     ip_address TEXT,
     user_agent TEXT,
     referrer TEXT,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     status TEXT DEFAULT 'new'
   );
   CREATE INDEX IF NOT EXISTS idx_leads_created ON leads(created_at);
   CREATE INDEX IF NOT EXISTS idx_leads_phone ON leads(phone);
   ```
7. `public/submit.php`:
   - `require_once '../src/Bootstrap.php'`
   - Check `$_SERVER['REQUEST_METHOD'] === 'POST'`
   - Verify CSRF token
   - Run honeypot check
   - Call `LeadService::process()`
   - On success: `echo json_encode(['ok' => true])` OR redirect to `/thanks.php`
   - On error: `http_response_code(400)` + JSON error
8. Add `public/.htaccess` rule to route POST `/submit` to `submit.php`

**Deliverables**: Secure form endpoint that validates, persists, and emails leads.

---

### Phase 3 — Templating & Refactor

**Goal**: Convert static HTML into reusable PHP views with JSON-driven content.

**Tasks**:

1. Create `data/packages.json` with all 12 packages (extract from current `index.html`):
   ```json
   [
     {
       "slug": "shimla",
       "title": "Shimla Tour Package",
       "image": "shimla.jpg",
       "duration": "3N/4D",
       "price": "On Request",
       "destinations": ["Shimla", "Kufri", "Chail", "Mall Road"],
       "itinerary": [
         {"day": 1, "title": "Arrival in Shimla", "desc": "..."},
         ...
       ],
       "inclusions": ["Flight", "Hotel", "Sightseeing", "Meals", "Transfers"]
     },
     ...
   ]
   ```
2. Create `data/reviews.json` with 6 testimonials
3. Create `views/partials/header.php`:
   - `<head>` with SEO meta, OG tags, Schema.org JSON-LD (LocalBusiness + TravelAgency)
   - Top contact bar (email + phone)
   - Hero section + hero form (left content stays static; right form is partial)
   - Bootstrap CSS + custom CSS
4. Create `views/partials/footer.php`:
   - Guarantee, Approved By, Payments, Customer Support sections
   - Floating WhatsApp (fixed position) — **fix phone URL**
   - Scripts (Bootstrap JS, main.js)
5. Create `views/partials/package-card.php` — receives `$package` variable, renders one card
6. Create `views/partials/review-card.php` — receives `$review` variable, renders one card
7. Create `views/partials/enquiry-modal.php` — reusable, CSRF-protected form
8. Create `views/pages/home.php`:
   ```php
   <?php
   $packages = json_decode(file_get_contents(__DIR__ . '/../data/packages.json'), true);
   $reviews  = json_decode(file_get_contents(__DIR__ . '/../data/reviews.json'), true);
   include __DIR__ . '/partials/header.php';
   ?>
   <!-- Hero, Packages loop, Reviews loop, About -->
   <?php foreach ($packages as $p) include __DIR__ . '/partials/package-card.php'; ?>
   ...
   <?php include __DIR__ . '/partials/footer.php'; ?>
   ```
9. Create `views/pages/thanks.php` — minimal thank-you with back-to-home CTA
10. Merge CSS:
    - `assets/css/variables.css` — CSS custom properties
    - `assets/css/main.css` — merge `style1.css` + `responsive.css`, all `@media` inside, BEM-ish naming
11. Delete old `css/style1.css` and `css/responsive.css`

**Deliverables**: Clean, maintainable, JSON-driven landing page (~300 lines instead of 2,500).

---

### Phase 4 — Lead-Capture Conversion Stack

**Goal**: Maximize lead capture with 4 surfaces and trust signals.

**Tasks**:

1. **Hero form** (keep existing) — above-the-fold, CSRF-protected
2. **Sticky mobile bottom-bar CTA** (`assets/js/main.js`):
   - Shows after scroll > 200px on screens < 768px
   - Two buttons: "📞 Call Now" + "💬 WhatsApp"
   - Dismissable for session
3. **Exit-intent modal** (`assets/js/main.js`):
   - Desktop only (`window.innerWidth > 992`)
   - Triggers on `mouseleave` from top of viewport
   - Once per session (sessionStorage flag)
   - "Wait! Get ₹500 OFF your first Himachal trip" with form
4. **Per-package "Get Free Quote"** → opens modal pre-filled with package name
5. **Floating WhatsApp widget** — fix phone URL, add hover tooltip
6. **Trust strip** below hero:
   - "Approved by Himachal Tourism"
   - "10,000+ Happy Travelers"
   - "4.9★ Google Rating"
   - Payment method icons
7. **JSON-LD Schema.org** (`LocalBusiness` + `TravelAgency` + `Product` per package):
   ```json
   {
     "@context": "https://schema.org",
     "@type": "TravelAgency",
     "name": "Mystical Expedition",
     "address": {...},
     "telephone": "+91-8219000937",
     "priceRange": "₹₹",
     "aggregateRating": {"ratingValue": "4.9", "reviewCount": "200"}
   }
   ```
8. **OG tags + Twitter Card** in `<head>` for social sharing
9. **Lazy-loaded images**: `loading="lazy" decoding="async" srcset="..."`
10. **Pre-fill destination** in modal when "Get Free Quote" clicked on a package card
11. **Form honeypot field**: hidden `<input name="website">` — bots fill it, humans don't
12. **Analytics**: GTM data layer event on successful submission (`event: 'generate_lead'`)

**Deliverables**: 4 capture surfaces, trust signals, SEO markup, analytics events.

---

### Phase 5 — Polish & Performance

**Goal**: Production-ready quality.

**Tasks**:

1. Lighthouse pass — target ≥ 90 on Performance, Accessibility, Best Practices, SEO
2. Accessibility audit:
   - All images have `alt`
   - Form labels associated with inputs
   - Color contrast ≥ 4.5:1
   - Keyboard navigation works
   - `aria-live` for form errors
3. Mobile responsive test: 320px, 375px, 414px, 768px, 1024px, 1440px
4. Cross-browser: Chrome, Firefox, Safari, Edge
5. Form E2E test: submit → check `data/leads.sqlite` has row → check email arrives
6. `robots.txt` + `sitemap.xml`
7. README.md with:
   - Local setup (`composer install`, copy `.env.example → .env`)
   - XAMPP DocumentRoot configuration
   - Hostinger subdomain setup steps
   - SMTP password rotation instructions
8. Add 404.php error page

**Deliverables**: Lighthouse 90+, accessible, tested, documented.

---

### Phase 6 — Deployment

**Goal**: Production-ready on subdomain.

**Tasks**:

1. On Hostinger:
   - Create subdomain (e.g., `book.mysticalexpedition.com`)
   - Point DocumentRoot to `public/`
   - Upload files via FTP/SFTP (or git push if SSH enabled)
   - Run `composer install --no-dev --optimize-autoloader` on server
   - Set `.env` with real SMTP credentials
   - Enable free SSL (Let's Encrypt)
2. Verify:
   - HTTPS works
   - Form submission succeeds
   - Email arrives
   - SQLite file created and grows
3. Submit `sitemap.xml` to Google Search Console
4. Set up cron to backup `data/leads.sqlite` weekly (optional)

**Deliverables**: Live, secure, performant landing page capturing leads.

---

## 5. Security Wins

| Before                                | After                                                         |
| ------------------------------------- | ------------------------------------------------------------- |
| SMTP password in `mail.php` (in repo) | `.env` on server only, `.env.example` in repo                 |
| `display_errors = 1` in production    | Errors logged to file, hidden from output                     |
| No CSRF protection                    | CSRF token on every form, timing-safe verify                  |
| No input validation                   | Whitelist + regex + `filter_var`                              |
| Bot spam unprotected                  | Honeypot field + rate-limit per IP                            |
| `vendor/` web-accessible              | Apache blocks via `.htaccess`                                 |
| `.env` web-accessible                 | Apache blocks via `.htaccess`                                 |
| No HTTPS enforcement                  | `.htaccess` forces HTTPS                                      |
| No security headers                   | CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy |
| Old PHPMailer 5.x autoloader          | PHPMailer 6.9 via Composer (security-patched)                 |

---

## 6. Conversion (Lead-Capture) Wins

| Before                              | After                                                              |
| ----------------------------------- | ------------------------------------------------------------------ |
| 1 capture form (hero)               | 4 capture surfaces (hero, sticky mobile, exit-intent, per-package) |
| Auto-popup on page load (intrusive) | Exit-intent modal (desktop) + sticky CTA (mobile)                  |
| WhatsApp number has typo            | Fixed `wa.me/918219000937` link                                    |
| No trust signals below hero         | Trust strip with approval, reviews, payments                       |
| No pre-filling                      | Modal pre-fills destination from clicked package                   |
| No JSON-LD                          | Rich Schema.org markup → Google rich results                       |
| No OG tags                          | Beautiful social media previews                                    |
| Static content                      | JSON-driven — easy to A/B test pricing/order                       |

---

## 7. File-by-File Change Summary

### New Files (~35)

| File                               | Purpose                             |
| ---------------------------------- | ----------------------------------- |
| `.gitignore`                       | Exclude vendor, .env, logs, .sqlite |
| `.env`                             | Local SMTP creds (gitignored)       |
| `.env.example`                     | Safe template                       |
| `.htaccess`                        | Root safety deny rules              |
| `composer.json`                    | Dependency manifest                 |
| `composer.lock`                    | Pinned versions                     |
| `schema.sql`                       | SQLite DDL                          |
| `README.md`                        | Setup + deploy docs                 |
| `PLAN.md`                          | This file                           |
| `logs/.gitkeep`                    | Keep empty folder                   |
| `public/.htaccess`                 | Security + cache + HTTPS            |
| `public/submit.php`                | Form endpoint                       |
| `public/robots.txt`                | SEO                                 |
| `public/sitemap.xml`               | SEO                                 |
| `public/index.php`                 | Landing (rewritten)                 |
| `public/thanks.php`                | Thank-you (rewritten)               |
| `src/Bootstrap.php`                | App bootstrap                       |
| `src/Database.php`                 | SQLite singleton                    |
| `src/Csrf.php`                     | CSRF tokens                         |
| `src/LeadService.php`              | Validate + persist                  |
| `src/MailService.php`              | PHPMailer wrapper                   |
| `views/partials/header.php`        | Top + nav                           |
| `views/partials/footer.php`        | Bottom + scripts                    |
| `views/partials/package-card.php`  | Looped                              |
| `views/partials/review-card.php`   | Looped                              |
| `views/partials/enquiry-modal.php` | Reusable form                       |
| `views/pages/home.php`             | Composed page                       |
| `views/pages/thanks.php`           | Composed thank-you                  |
| `data/packages.json`               | 12 packages                         |
| `data/reviews.json`                | 6 testimonials                      |
| `assets/css/variables.css`         | Design tokens                       |
| `assets/css/main.css`              | Merged styles                       |
| `assets/js/main.js`                | UI behaviors                        |
| `assets/js/validation.js`          | Form validation                     |

### Deleted Files

| File                 | Reason                                    |
| -------------------- | ----------------------------------------- |
| `PHPMailer/`         | Replaced by `vendor/phpmailer/phpmailer/` |
| `index.html`         | Replaced by `public/index.php`            |
| `thanks.html`        | Replaced by `public/thanks.php`           |
| `mail.php`           | Replaced by `public/submit.php`           |
| `css/style1.css`     | Merged into `assets/css/main.css`         |
| `css/responsive.css` | Merged into `assets/css/main.css`         |
| `Himachal/`          | Orphaned folder with single unused file   |

### Modified Files

None — every existing file is either kept (images), moved, or replaced.

---

## 8. Risks & Mitigations

| Risk                                                | Mitigation                                                                         |
| --------------------------------------------------- | ---------------------------------------------------------------------------------- |
| Subdomain DocumentRoot misconfiguration breaks site | Document both subdomain + XAMPP setup in README                                    |
| SQLite locked if two requests hit at once           | Use PDO with `SQLITE_OPEN_READWRITE \| SQLITE_OPEN_CREATE` + retry on busy         |
| SMTP auth fails (password rotated)                  | `.env.example` documented; clear error in logs                                     |
| Email goes to spam                                  | Use SPF/DKIM records (Hostinger default); include unsubscribe link in confirmation |
| Composer install fails on shared hosting            | Document `--no-dev` flag; provide fallback manual PHPMailer instructions           |
| Bootstrap bundle JS conflicts with custom JS        | Use Bootstrap 5 ESM; namespace custom code under `window.ME = {}`                  |
| Image filenames have spaces or mixed case           | Normalize all filenames to lowercase-kebab-case during refactor                    |

---

## 9. Timeline

| Phase                      | Estimated effort                       |
| -------------------------- | -------------------------------------- |
| Phase 1 — Foundation       | 1 session                              |
| Phase 2 — Backend PHP      | 2 sessions                             |
| Phase 3 — Templating       | 2 sessions                             |
| Phase 4 — Conversion stack | 1 session                              |
| Phase 5 — Polish           | 1 session                              |
| Phase 6 — Deploy           | 1 session                              |
| **Total**                  | **~8 sessions** (≈ 1 working day each) |

---

## 10. Approval Checklist

Before I start Phase 1, confirm:

- [ ] Plan reviewed and approved
- [ ] Tech stack (PHP + Composer + SQLite + Bootstrap 5.3) confirmed
- [ ] Folder structure acceptable
- [ ] Security & conversion wins align with expectations
- [ ] No missing requirements (e.g., payment gateway, multi-language, blog)
- [ ] Ready for `git init` baseline commit

---

**Prepared by**: GitHub Copilot
**Status**: ✅ Ready for approval — awaiting "go" command to begin Phase 1
