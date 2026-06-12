# Lumisk System — Project Guide

Invoicing & estimates app for Lumisk Technology: admin panel + client portal, PDF generation, and email sending.

## Stack

- **Laravel 11**, **Livewire 3.5+**, **Alpine.js 3**, **Tailwind CSS 3** (`@tailwindcss/forms`)
- **PHP 8.5**, MySQL, Vite for assets
- **barryvdh/laravel-dompdf 3** for PDFs
- Auth scaffolded from **Laravel Breeze**
- Tests: **PHPUnit 10**

## Commands

- `npm run dev` — Vite dev server (creates `public/hot`; see gotcha below)
- `npm run build` — compile assets for production
- `php artisan migrate` — run migrations (use `--force` in non-interactive shells)
- `vendor/bin/phpunit --no-coverage` — run the test suite (19 tests)
- `vendor/bin/pint` — code style

## Structure

- `app/Livewire/Admin/` — admin UI components: `Dashboard`, `Clients/`, `Invoices/`, `Estimates/`, `SavedItems/`, `Settings/`, `SendEmailModal`
- `app/Models/` — `Client`, `Company`, `Invoice`/`InvoiceItem`, `Estimate`/`EstimateItem`, `SavedItem`, `EmailLog`, `User`
- `app/Services/DocumentNumberService.php` — `nextInvoiceNumber()` / `nextEstimateNumber()` (the `*_number` columns have no DB default; always set via this)
- `app/Support/PdfRenderer.php` — `PdfRenderer::invoice($invoice)` / `::estimate($estimate)` → DomPDF instances
- `app/Helpers/EmailTemplateHelper.php` — `{placeholder}` replacement for email subject/body
- `app/helpers.php` — global helpers incl. `company_settings()` and `money()`
- `resources/views/pdf/` — `invoice.blade.php`, `estimate.blade.php` (self-contained, not using a shared partial)
- `resources/views/emails/` — HTML email bodies
- `routes/` — `admin.php` (`admin.auth`/`admin.guest`), `portal.php` (`client.auth`/`client.guest`), `web.php`

## Conventions

- **Single company row**: `Company::settings()` (or `company_settings()`) returns the id=1 row, creating it if missing. `Company` is `$guarded = []`, so new columns are mass-assignable with no model change.
- **Document statuses**: Invoice = draft, sent, paid, overdue, cancelled. Estimate = draft, sent, accepted, rejected, expired. Use the model `STATUSES` constants — don't invent values.
- **Totals**: line totals and document totals are recomputed via the model's `recalculateTotals()` on save.
- **Livewire computed properties** use the legacy `getXxxProperty()` syntax (accessed as `$this->subtotal`, etc.) — this is still supported in Livewire 3.
- **Nullable numeric form fields**: number inputs bound with `wire:model.live` send `null` when cleared. Any property such a field binds to **must be nullable** (`public ?float $x = 0;`) and coalesced `?? 0` where used, or Livewire throws `PropertyNotFoundException` on clear. Applies to `tax_rate`/`discount_amount` in `InvoiceForm`/`EstimateForm`.

## Gotchas

- **Stale `public/hot`**: `npm run dev` writes `public/hot` pointing at the Vite dev server. If the dev server is stopped, every asset 404s and pages break/redirect. Fix: delete `public/hot`.
- **PHP 8.5 deprecation noise**: CLI `artisan`/`tinker` print `PDO::MYSQL_ATTR_SSL_CA deprecated` from vendor code. It's suppressed for web in `public/index.php`. Filter in shells with `grep -iv "deprecat\|MYSQL"`.
- **DomPDF `@page` margins are ignored** — set page margins on `body` instead. See the dedicated note in auto-memory (`dompdf-pdf-rendering.md`) for the working pattern (body margin, fixed footer, em-based font scaling, forced page-2 break) and how to visually verify PDFs with PyMuPDF.

## Email

SMTP is Hostinger (`smtp.hostinger.com:465`, SSL). `MAIL_PASSWORD` in `.env` is a placeholder and must be set to the real account password before email sending works.
