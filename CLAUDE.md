# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Innovative Homesi is a PHP furniture e-commerce site (root deployment, no framework) deployed to Hostinger shared hosting. Production domain: `https://innovativehomesi.com/`. Target PHP `>=7.4`. Currency INR; timezone `Asia/Kolkata`.

## Setup & common commands

There is no build step, no test runner, no linter — it's a vanilla PHP app served by Apache/XAMPP.

```bash
composer install                               # installs phpseclib (runtime) + phpoffice/phpspreadsheet (dev, used by admin import/export)
php -S localhost:8000                          # quick local server (DB still required)
php -r "echo bin2hex(random_bytes(32));"       # generate ORDER_TOKEN_SECRET (referenced in .env)
php test_hostinger_mail.php                    # one-off SMTP smoke test (root file)
```

Database: import `sql/innovative_homesi_complete.sql` into MySQL. Connection settings come from `.env` (loaded by `includes/config.php`); `.env` is gitignored. Local dev typically uses XAMPP — the DB error page in [includes/db.php](includes/db.php) points to phpMyAdmin and a DB named `innovative`, but production uses `u312948055_innovative`.

## Architecture

**Front-controller-less PHP.** Every public page is a top-level `.php` file (`index.php`, `shop.php`, `product-detail.php`, `checkout.php`, `login.php`, …). Apache routes by filename; `.htaccess` only handles HTTPS redirect, security headers, and blocks direct access to `includes/`, `sql/`, `vendor/`, and dotfiles.

**Single bootstrap.** Every page (public, ajax, auth, api, webhook) starts with:

```php
require_once __DIR__ . '/includes/init.php';
```

[includes/init.php](includes/init.php) loads, in order: `config.php` → `session.php` → `db.php` → `functions.php` → `security.php`, then calls `setSecurityHeaders()`, `generateCsrfToken()`, and `autoLoginFromCookie($pdo)`. After init you have globals `$pdo` (PDO, primary) and `$conn` (mysqli, kept for legacy modules) — both connect to the same DB. New code should prefer `$pdo` with prepared statements.

**Directory layout by role** (not MVC):
- Top-level `*.php` — user-facing pages, each renders header → page body → footer via includes from `includes/`.
- `ajax/` — POST endpoints called by JS (cart, wishlist, coupons, contact, order creation, payment processing). Return JSON. Most call `verifyCsrfOrDie()`.
- `api/` — read-only JSON endpoints consumed by frontend JS (products, blog, gallery, wishlist).
- `auth/` — form handlers for login/register/logout plus MSG91 OTP send/verify endpoints. Posted to from `login.php`/`register.php`.
- `admin/` — separate admin UI with its own `login.php`, `includes/header.php` (gates on `$_SESSION['admin_id']`), and own `ajax/` subdir for variation CRUD. Admin reuses the public `includes/` bootstrap for DB/config but has its own header/footer.
- `includes/` — shared library: config, DB, session, security (CSRF, rate limit, headers, CSP), helpers, mail (PHPMailer wrapper in `mail-helper.php` + SMTP config in `mail-config.php`), Razorpay config, MSG91 + OTP helpers, header/footer/topbar partials.
- `webhook.php` — Razorpay webhook receiver at site root; verifies HMAC against `RAZORPAY_WEBHOOK_SECRET`.
- `PHPMailer/` — vendored PHPMailer (separate from Composer `vendor/`).

**Sessions & auth.** Two parallel auth realms keyed by separate session vars: customers use `$_SESSION['user_id']` (helpers `isLoggedIn()`, `isAdmin()` in `includes/functions.php`); admin uses `$_SESSION['admin_id']` (gated in `admin/includes/header.php`). Session cookie is `innovative_session`, HttpOnly, SameSite=Strict, Secure when HTTPS. Session ID regenerates every 30 minutes. Customer "remember me" is handled by `autoLoginFromCookie()` invoked from `init.php`.

**Security model.** Every state-changing endpoint must call `verifyCsrfOrDie()` (or `validateCsrfToken()` then redirect). CSP is set in `setSecurityHeaders()` and explicitly allow-lists Razorpay origins (`checkout.razorpay.com`, `api.razorpay.com`) — when adding new third-party scripts/iframes, update the CSP in [includes/security.php](includes/security.php) or they'll be blocked. Rate limiting is session-based via `checkRateLimit($pdo, $action, $maxHits, $window)`.

**Payments.** Razorpay live mode. Flow: `checkout.php` → `ajax/create-order.php` (creates Razorpay order, returns order_id) → frontend Razorpay checkout → `ajax/process-payment.php` (verifies signature, marks order paid) → `order-success.php`. `webhook.php` is the async source of truth — never trust client-side payment confirmation alone. `ORDER_TOKEN_SECRET` from `.env` signs order tokens passed between checkout and success pages.

**Email & SMS.** Transactional email via Hostinger SMTP through PHPMailer ([includes/mail-helper.php](includes/mail-helper.php)). OTP delivery via MSG91 ([includes/msg91-helper.php](includes/msg91-helper.php)) — auth keys live in `.env` (currently placeholders). Generic OTP storage/verification logic in [includes/otp-helper.php](includes/otp-helper.php).

## Conventions to preserve

- Always go through `init.php` — don't call `session_start()` or instantiate PDO in page files. (Admin sub-includes are the exception and intentionally start their own session before checking `admin_id`.)
- Use `$pdo` with prepared statements for new queries; only touch `$conn` (mysqli) when extending a module that already uses it.
- Sanitize output with `htmlspecialchars(...)` / the `sanitize()` helper from `includes/functions.php`; format prices with `formatPrice()` (renders `₹` + `number_format($n, 2)`).
- `BASE_URL_PATH` is `''` (root deployment). Don't hardcode subdirectory paths in URLs — derive from `SITE_URL` / `ADMIN_URL` constants.
- `.env`, `vendor/`, `composer.lock`, `uploads/`, and `includes/config.php` are gitignored. The `.htaccess` denies direct HTTP access to `.env`, `.sql`, `.log`, `.json`, `.lock`, `.md`, etc., plus the `includes/`, `sql/`, `vendor/` directories — keep new secrets in `.env`, not in code.
- Production sets `APP_ENV=production` which silences `display_errors`. When debugging in production, tail the server error log rather than echoing — and never commit a switch back to development mode.
