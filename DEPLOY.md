# Deployment Guide — Hostinger Shared Hosting

**Target:** `shimla.mysticalexpedition.com`
**Stack:** PHP 8.2 + Bootstrap 5.3 + SQLite + PHPMailer
**DocumentRoot on server (fixed):** `/home/u335140513/domains/mysticalexpedition.com/public_html/shimla/`
**Project layout on server:** Entire project lives INSIDE the DocumentRoot, with private app files under `public_html/shimla/_app/` (auto-blocked from HTTP).

---

## Why the `_app/` subfolder?

Hostinger locks the subdomain's DocumentRoot to `public_html/shimla/`. The project is designed to keep app secrets and PHP-source files (`src/`, `vendor/`, `views/`, `data/`, `logs/`, `.env`) **inside** that web root. We compensate by placing them in a `_app/` subfolder blocked by a `.htaccess` deny rule (returns 403 to any HTTP request).

Layout on production server:

```
public_html/shimla/                          ← DocumentRoot, publicly served
├── index.php, submit.php, thanks.php,       ← web entry points
│    404.php, 500.php
├── .htaccess                                ← HTTPS + headers + cache
├── robots.txt, sitemap.xml
├── assets/                                  ← CSS, JS, images (public)
└── _app/                                    ← ALL private app files
    ├── .htaccess                            ← require all denied
    ├── .gitignore                           ← ignores vendor/, .env, sqlite, logs
    ├── .env                                 ← secrets (created on server)
    ├── composer.json, composer.lock
    ├── src/                                 ← PHP source (autoloaded)
    ├── views/                               ← PHP templates
    ├── data/                                ← packages.json, reviews.json,
    │                                        ← leads.sqlite (created at runtime)
    ├── logs/                                ← error.log (created at runtime)
    └── vendor/                              ← installed by composer install
```

Local project layout mirrors the same shape (the entire project is checked into Git):

```
mystical/
├── .gitignore, README.md, PLAN.md, DEPLOY.md
├── diag/, scripts/, lint.ps1
└── public/                                  ← entry points + assets
    ├── index.php, submit.php, thanks.php, 404.php, 500.php
    ├── .htaccess, robots.txt, sitemap.xml
    ├── assets/
    └── _app/                                ← src/views/data/vendor/.env/composer.*
        ├── .htaccess
        └── ...
```

---

## Pre-flight (Local — already done)

- [x] `public/_app/.htaccess` denies all HTTP access (belt + suspenders)
- [x] `public/_app/.gitignore` keeps `vendor/`, `.env`, `data/*.sqlite`, `logs/*.log` out of GitHub
- [x] Root `.gitignore` ignores leftover legacy paths (`/data/`, `/logs/`, `/vendor/`, `/composer.phar`)
- [x] All entry points use `require __DIR__ . '/_app/...'` so they reach the private folder
- [x] `Bootstrap::projectRoot()` walks up via `composer.json` lookup — works in both layouts
- [x] Smoke test: 13/13 routes pass, all PHP files lint clean
- [x] Security tests: `.env`, `vendor/`, `src/`, `data/` all return 403

---

## Step 1 — Upload the project to Hostinger

In **hPanel → File Manager** or via **SFTP (FileZilla)**, navigate to:

```
/home/u335140513/domains/mysticalexpedition.com/public_html/shimla/
```

The cleanest upload is to upload **only the contents of your local `public/` folder** so that:

| Your local file                              | Lands at on server                      |
| -------------------------------------------- | --------------------------------------- |
| `public/index.php`                           | `public_html/shimla/index.php`          |
| `public/submit.php`                          | `public_html/shimla/submit.php`         |
| `public/.htaccess`                           | `public_html/shimla/.htaccess`          |
| `public/assets/`                             | `public_html/shimla/assets/`            |
| `public/robots.txt`                          | `public_html/shimla/robots.txt`         |
| `public/sitemap.xml`                         | `public_html/shimla/sitemap.xml`        |
| `public/_app/`                               | `public_html/shimla/_app/`              |
| `public/_app/.htaccess`                      | `public_html/shimla/_app/.htaccess`     |
| `public/_app/.gitignore`                     | `public_html/shimla/_app/.gitignore`    |
| `public/_app/src/`                           | `public_html/shimla/_app/src/`          |
| `public/_app/views/`                         | `public_html/shimla/_app/views/`        |
| `public/_app/data/` (without `leads.sqlite`) | `public_html/shimla/_app/data/`         |
| `public/_app/composer.json`                  | `public_html/shimla/_app/composer.json` |
| `public/_app/composer.lock`                  | `public_html/shimla/_app/composer.lock` |

**DO NOT upload:**

- `public/_app/.env` — create on the server (Step 3)
- `public/_app/vendor/` — install via composer on the server (Step 2)
- `public/_app/data/leads.sqlite` — created at runtime
- `public/_app/logs/error.log` — created at runtime

---

## Step 2 — SSH into the server and install dependencies

Enable SSH in hPanel → **Advanced** → **SSH Access**.

```bash
ssh u335140513@shimla.mysticalexpedition.com -p 65002

cd /home/u335140513/domains/mysticalexpedition.com/public_html/shimla/_app

# Verify PHP version (need 8.1+)
php -v

# Install Composer if needed
ls /usr/local/bin/composer* 2>/dev/null
# If missing:
# curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP deps
composer install --no-dev --optimize-autoloader
```

This creates `public_html/shimla/_app/vendor/` on the server.

---

## Step 3 — Create `public_html/shimla/_app/.env`

Via SSH:

```bash
cd /home/u335140513/domains/mysticalexpedition.com/public_html/shimla/_app
nano .env
```

Paste (substituting real values):

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shimla.mysticalexpedition.com
APP_NAME="Mystical Expedition"

BUSINESS_NAME="Mystical Expedition"
BUSINESS_EMAIL="info@mysticalexpedition.com"
BUSINESS_PHONE="+91-8219000937"
BUSINESS_ADDRESS="NH-22, opposite SBI Bank, Shoghi, Himachal Pradesh 171219"
BUSINESS_WHATSAPP="918219000937"

LEADS_NOTIFY_EMAIL="info@mysticalexpedition.com"

MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=info@mysticalexpedition.com
MAIL_PASSWORD=YOUR_REAL_HOSTINGER_MAIL_PASSWORD_HERE
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@mysticalexpedition.com
MAIL_FROM_NAME="Mystical Expedition"

LEADS_RATE_LIMIT_PER_HOUR=5

DB_PATH=data/leads.sqlite
```

Save with `Ctrl+O`, Enter, then `Ctrl+X` to exit.

### Lock down permissions

```bash
chmod 640 .env                 # owner read+write, group read
chmod -R 755 src views
chmod -R 775 data logs         # Apache needs to write here
```

If `data/` or `logs/` aren't writable by Apache, the SQLite DB and error log will fail. If `chmod` isn't enough, contact Hostinger support to set the folders to group-writable.

---

## Step 4 — Enable SSL

hPanel → **Security** → **SSL/TLS** → toggle **Free SSL** on for `shimla.mysticalexpedition.com`. Takes 5–15 minutes to provision.

Once active, your `public_html/shimla/.htaccess` will auto-redirect HTTP → HTTPS.

---

## Step 5 — Smoke tests

Run these in a browser:

| URL                                                            | Expected | What it verifies        |
| -------------------------------------------------------------- | -------- | ----------------------- |
| `https://shimla.mysticalexpedition.com/`                       | 200      | Homepage renders        |
| `https://shimla.mysticalexpedition.com/thanks.php`             | 200      | Template OK             |
| `https://shimla.mysticalexpedition.com/assets/css/main.css`    | 200      | CSS reachable           |
| `https://shimla.mysticalexpedition.com/assets/js/main.js`      | 200      | JS reachable            |
| `https://shimla.mysticalexpedition.com/.env`                   | **403**  | Root-level .env ignored |
| `https://shimla.mysticalexpedition.com/_app/.env`              | **403**  | `_app/` blocked         |
| `https://shimla.mysticalexpedition.com/_app/`                  | **403**  | `_app/` blocked         |
| `https://shimla.mysticalexpedition.com/_app/vendor/`           | **403**  | vendor blocked          |
| `https://shimla.mysticalexpedition.com/_app/src/`              | **403**  | src blocked             |
| `https://shimla.mysticalexpedition.com/_app/data/leads.sqlite` | **403**  | DB blocked              |
| `https://shimla.mysticalexpedition.com/this-does-not-exist`    | 404      | Custom 404 page         |

Then **submit a real test lead** via the homepage form:

1. Fill name + phone + email + destination
2. Click "Plan My Trip"
3. Verify it redirects to `https://shimla.mysticalexpedition.com/thanks.php`
4. SSH in and confirm the row landed in `_app/data/leads.sqlite`:

   ```bash
   cd /home/u335140513/domains/mysticalexpedition.com/public_html/shimla/_app
   php -r '$db = new PDO("sqlite:data/leads.sqlite"); foreach ($db->query("SELECT name, email, phone, created_at FROM leads ORDER BY id DESC LIMIT 5") as $r) { echo "$r[created_at]  $r[name]  $r[email]  $r[phone]\n"; }'
   ```

5. Check `LEADS_NOTIFY_EMAIL` inbox — should have the new lead email
6. Check the email you submitted — should have the confirmation

---

## Step 6 — Backups and monitoring

### Daily SQLite backup (hPanel cron)

hPanel → **Advanced** → **Cron Jobs** → add:

```cron
0 2 * * * cd /home/u335140513/domains/mysticalexpedition.com/public_html/shimla/_app && tar czf /home/u335140513/backups/leads-$(date +\%Y\%m\%d).tar.gz data/leads.sqlite
```

### Uptime monitoring (free)

[UptimeRobot](https://uptimerobot.com) → free tier → checks `https://shimla.mysticalexpedition.com/` every 5 min, emails you if down.

### Error log review (weekly)

```bash
ssh u335140513@shimla.mysticalexpedition.com -p 65002
tail -100 /home/u335140513/domains/mysticalexpedition.com/public_html/shimla/_app/logs/error.log
```

---

## Troubleshooting

| Symptom                                            | Cause                                          | Fix                                                                           |
| -------------------------------------------------- | ---------------------------------------------- | ----------------------------------------------------------------------------- |
| Every page returns 500                             | PHP version too low                            | hPanel → **MultiPHP Manager** → set to PHP 8.2                                |
| "Class 'Dotenv\Dotenv' not found"                  | `vendor/` missing or wrong PHP version         | Re-run `composer install`; check `php -v` matches `composer.json`             |
| Form submits but no email sent                     | `MAIL_PASSWORD` is wrong, or SMTP port blocked | Test SMTP separately; check `_app/logs/error.log`                             |
| `data/leads.sqlite` not created                    | Folder not writable                            | `chmod 775 data`; if still failing, ask Hostinger support                     |
| `.htaccess` is being ignored                       | MultiPHP manager override                      | hPanel → MultiPHP → ensure per-folder `.htaccess` allowed                     |
| Lead submitted but no DB row                       | SQLite file path wrong                         | Check `.env` `DB_PATH=data/leads.sqlite` (relative, resolves to `_app/data/`) |
| Image/font 404 in browser console                  | CSP in `public/.htaccess` too strict           | Make sure the CSP allows `cdn.jsdelivr.net` and `cdnjs.cloudflare.com`        |
| `shimla/_app/` blocked (good!) but app also broken | `.htaccess` overly restrictive                 | First test `https://shimla/...` returns 200; if so, app is fine               |

---

## Future deployments (recommended workflow)

Once the initial deploy works, all future updates are just:

```bash
# Local: commit + push
git add . && git commit -m "..."
git push origin main

# Server: pull + (only if composer deps changed) reinstall
cd /home/u335140513/domains/mysticalexpedition.com/public_html/shimla
git pull origin main
cd _app && composer install --no-dev --optimize-autoloader   # only if composer.json changed
```

No manual file uploads needed.

## Rollback

If a deployment breaks something critical, the previous version is always in the GitHub commit history:

```bash
cd /home/u335140513/domains/mysticalexpedition.com/public_html/shimla
git fetch origin
git reset --hard <previous-commit-sha>
```
