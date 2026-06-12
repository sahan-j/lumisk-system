# Lumisk System

Business management system for **Lumisk Technology** — invoices, estimates, clients,
and a client portal. Built with Laravel 11, Livewire 3, Alpine.js, Tailwind CSS v3,
MySQL and DomPDF.

There are two independent areas:

| Area          | URL prefix | Auth guard | Login page       |
|---------------|------------|------------|------------------|
| Admin Panel   | `/admin`   | `web`      | `/admin/login`   |
| Client Portal | `/portal`  | `client`   | `/portal/login`  |

---

## Default credentials (after seeding)

**Admin** — `admin@lumisktechnology.com` / `admin123`
Change the password immediately in production (re-seed with a new value or update via tinker).

Clients are created by the admin under **Admin → Clients**. A client can only sign in to
the portal when **Portal Access** is enabled and a password has been set.

---

## Local development

Requirements: PHP 8.2+, Composer, Node 18+, MySQL.

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
# edit .env → set DB_DATABASE / DB_USERNAME / DB_PASSWORD

# 3. Database
php artisan migrate --seed

# 4. Storage symlink (for uploaded company logo)
php artisan storage:link

# 5. Build assets (or `npm run dev` while developing)
npm run build

# 6. Serve
php artisan serve
```

Visit `http://localhost:8000` → redirects to the admin login.

### Tests

```bash
php artisan test
```

Tests run against a separate `lumisk_system_test` MySQL database (configured in `phpunit.xml`).
Create it once: `CREATE DATABASE lumisk_system_test;`

---

## Deploying to cPanel (shared hosting)

Target: `https://system.lumisktechnology.com`

### 1. Build assets locally, then upload

```bash
npm run build
```

Upload the whole project (including `vendor/` and `public/build/`) to the hosting account,
e.g. into `~/lumisk-system`. Building locally avoids needing Node on the server.

### 2. Point the document root at `public/`

In cPanel **Domains / Subdomains**, set the document root of
`system.lumisktechnology.com` to `…/lumisk-system/public`.

If you cannot change the document root and it must stay at the project root, the included
root `.htaccess` transparently forwards requests into `public/`.
Laravel's own `public/.htaccess` handles front-controller routing.

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://system.lumisktechnology.com

DB_DATABASE=<cpanel_db_name>
DB_USERNAME=<cpanel_db_user>
DB_PASSWORD=<cpanel_db_password>
DB_COLLATION=utf8mb4_unicode_ci
```

`utf8mb4_unicode_ci` is used for broad MySQL/MariaDB compatibility on shared hosting.

### 4. Database + storage

```bash
php artisan migrate --force --seed
php artisan storage:link
```

If `storage:link` is not permitted on the host, create the symlink manually or copy
`storage/app/public` to `public/storage`.

### 5. Optimise for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Re-run these after any `.env` or code change. To clear: `php artisan optimize:clear`.

### 6. Permissions

Ensure `storage/` and `bootstrap/cache/` are writable by the web server (typically `755`).

No queue workers or cron jobs are required for Phase 1 — the queue uses the `sync` driver
and PDFs are generated on demand.

---

## Project structure

```
app/
  Http/Controllers/Admin/      Admin auth + PDF download controllers
  Http/Controllers/Portal/     Portal auth + PDF download controllers
  Http/Middleware/             AdminAuth, ClientAuth, RedirectIfAdmin, RedirectIfClient
  Livewire/Admin/              Dashboard, Clients, Invoices, Estimates, SavedItems, Settings
  Livewire/Portal/             Dashboard, Invoices, Estimates
  Models/                      Company, Client, SavedItem, Invoice(+Item), Estimate(+Item)
  Services/DocumentNumberService.php   INV-/EST- number generation
  Support/PdfRenderer.php      DomPDF wrapper
resources/views/
  components/layouts/          admin / portal / guest shells
  components/                  brand, status-badge, toasts, theme-toggle, app-modal, document-preview
  livewire/                    all component views
  pdf/                         dark-branded invoice + estimate PDF templates
routes/
  web.php                      root redirect
  admin.php                    /admin routes (admin. name prefix)
  portal.php                   /portal routes (portal. name prefix)
```

## Document numbering

Invoice/estimate numbers (`INV-001`, `EST-001`, …) are generated from the prefix and
counter stored in **Settings**. Counters increment atomically on create and are never
reused, even after a document is deleted.
