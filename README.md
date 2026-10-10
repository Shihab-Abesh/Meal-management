# Office Meal & Catering Management System

A single-vendor web app for a catering business that supplies daily meals to client offices. It replaces the notebook used to count meals, and it tracks orders, riders, deliveries, billing and reports.

**Stack:** Laravel 12 · PHP 8.2+ · MySQL/MariaDB · Inertia.js v3 · React 19 · Tailwind CSS 4

## Requirements

- PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` (XAMPP's PHP works)
- Composer 2
- Node.js 20+ and npm
- MySQL 8 or MariaDB 10.4+

## Installation

```bash
composer install
cp .env.example .env          # on Windows: copy .env.example .env
php artisan key:generate
```

Create an empty database (for example `meal_management`). Then check the `DB_*` values in `.env`.

Before seeding, set the first admin account in `.env`:

```dotenv
SEED_ADMIN_NAME="Vendor Admin"
SEED_ADMIN_EMAIL=admin@example.com
SEED_ADMIN_PHONE=01700000000
SEED_ADMIN_PASSWORD=change-me
```

Then run:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Open http://127.0.0.1:8000 and sign in with the admin email **or** phone number.

In local development (`APP_ENV=local`), the seeder also adds demo data: four clients with employees, menus, three weeks of meals, orders, riders, last month's bills and payments, and these accounts (password `password`):

| Account | Signs in with |
|---|---|
| Vendor staff | `staff@example.com` |
| Client admin (XYZ Software Ltd.) | `xyz@example.com` |
| Client admin (ABC Bank Ltd.) | `abcbank@example.com` |
| Rider (Rahim Uddin) | `01800000001` |

The demo seeder only fills empty tables, so running it again never changes existing data.

### Development

Run `npm run dev` alongside `php artisan serve` for hot reload, or start everything at once with `composer run dev`.

### Running under XAMPP (Apache)

When the project is inside `C:\xampp\htdocs\<folder>`, start Apache and MySQL in the XAMPP Control Panel, then open `http://localhost/<folder>/`. It redirects to `http://localhost/<folder>/public/`.

The `.htaccess` file in the project root only lets Apache serve the `public/` folder. Keep that file: without it, `.env` (your app key and database password) can be downloaded by anyone who can reach your PC.

## What the app does

| Area | For whom | What it covers |
|---|---|---|
| Daily meals | Vendor, clients | YES/NO meal sheet per client and date, bulk actions, live totals, cutoffs with admin override, calendar, history |
| Orders and deliveries | Vendor | Orders created automatically from confirmed meals, kitchen steps, rider assignment, delivery board |
| Rider app | Riders | Today's deliveries, address and call buttons, Picked up → On the way → Delivered, history |
| Clients and employees | Vendor, clients | Client organizations, their logins, employees with CSV import |
| Menu and pricing | Vendor | Menu items, a menu chart (week or month of lunch and dinner, edited in place, copy last week), client-specific prices with effective dates, default prices |
| Notifications | Vendor | Bell with live alerts when a client places, changes or cancels an order, when an order is delivered and when bills become overdue; toast, optional chime and desktop alerts; per-user choice of alerts |
| Billing | Vendor, clients | Bills from confirmed meals, payments (cash, bank, bKash, Nagad), due and overdue tracking, monthly statements |
| Reports | Vendor, clients | Daily, monthly, client, employee, menu and rider reports with charts, CSV (Excel) and PDF export |
| Settings | Vendor admin | Default cutoffs and prices, billing terms, email notifications, users and permissions, activity log |

## Configuration

Most settings are edited inside the app: business details under **Vendor Profile**; default cutoff times, default prices, payment terms and email notifications under **Settings**; vendor accounts and their permissions under **Users**. The remaining settings are in `.env` and `config/catering.php`:

| Setting | Where |
|---|---|
| Currency code and symbol | `APP_CURRENCY`, `APP_CURRENCY_SYMBOL` |
| Time zone (used for meal cutoffs and due dates) | `APP_TIMEZONE` |
| Outgoing email (bill, payment and delivery notices) | `MAIL_*` |
| First-run defaults for settings | `config/catering.php` → `defaults` |
| Business type suggestions | `config/catering.php` → `business_types` |
| Logo and menu photo size and file types | `config/catering.php` → `logo`, `menu_image` |
| Brand colour | `resources/css/app.css` → `--color-primary-*` |

### Scheduled task

Bills that are still unpaid after their due date are marked **Overdue** every night by `php artisan bills:mark-overdue`. Screens already show overdue bills correctly without it, but the stored status (used by exports and filters on other systems) needs the scheduler to run every minute:

- Linux: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1` in the crontab.
- Windows: a Task Scheduler task that runs `C:\xampp\php\php.exe C:\path\to\app\artisan schedule:run` every minute.

## Roles

| Role | Access |
|---|---|
| Vendor Admin | Everything |
| Vendor Staff | Operational modules (clients, employees, meals, menu, orders, riders, deliveries, reports) by default. Prices, billing, financial figures, settings, users and the activity log need an explicit permission, set under **Users**. |
| Client Admin | Their own organization only: dashboard, employees, daily meals, meal history, today's menu, billing, reports, profile |
| Rider | Their assigned deliveries only, never amounts or other clients |

Permissions are defined in `app/Enums/Permission.php`. Each one is registered as a Laravel Gate, so the same check works with `$user->can()`, the `can:` route middleware and policies. Client data is isolated on the server: client accounts are always scoped to their own organization, whatever IDs a request contains.

## Going live (production checklist)

1. In `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`, `LOG_LEVEL=warning`, `SESSION_SECURE_COOKIE=true` (when served over HTTPS), and real `MAIL_*` settings.
2. Use a strong `SEED_ADMIN_PASSWORD` before the first `php artisan migrate --seed`, and change it after signing in. With `APP_ENV=production` no demo data is created.
3. Install and build: `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`.
4. Prepare Laravel: `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize`.
5. Point the web server at the `public/` folder only (or keep the root `.htaccess` under XAMPP) and serve the app over HTTPS.
6. Set up the scheduled task above and regular database backups.

Destructive commands such as `migrate:fresh` are blocked in production.

## Project structure

```
app/
  Enums/            Roles, permissions and every status (orders, bills, payments, meals)
  Http/
    Controllers/    Thin controllers, one per screen area
    Middleware/     Inertia shared data, role check, inactive-user sign-out, security headers
    Requests/       Form Request validation for every write
    Resources/      Data sent to the frontend (no sensitive fields)
  Models/           Eloquent models
  Notifications/    Emails to client accounts
  Policies/         Client, employee and bill access rules
  Services/         Business logic: meals, orders, pricing, billing, reports, settings
  Support/          Money (exact decimals), navigation, phone, search helpers
config/catering.php Business configuration and setting defaults
resources/js/
  components/ui/    Reusable UI kit (Button, Input, Select, Card, Modal, Drawer, DataTable, ...)
  components/charts Column chart and ranked bars (no chart library)
  components/...    Feature components (billing, orders, rider, reports, ...)
  layouts/          App shell and sign-in layout
  pages/            One file per screen, matching Inertia::render() names
  hooks/, lib/      Shared hooks and formatting helpers
resources/views/    Root page and the PDF report template
routes/web.php      App routes; routes/auth.php for sign-in and password reset; routes/console.php for the scheduler
tests/              Feature and unit tests
docs/               Specification and milestone reports
```

## Tests

```bash
php artisan test
```

By default the tests use in-memory SQLite. To run them against MySQL, create a separate database first. Never point the tests at your real database: they wipe it.

```bash
DB_CONNECTION=mysql DB_DATABASE=meal_management_testing php artisan test
```

## Troubleshooting

- **Startup warnings about `oci8_12c` / `oci8_19` (XAMPP):** these come from Oracle extensions enabled in `C:\xampp\php\php.ini`. They are harmless. To silence them, comment out the `extension=oci8_12c` and `extension=oci8_19` lines.
- **Logo not showing:** run `php artisan storage:link`.
- **Blank page after pulling changes:** run `npm run build` again, or keep `npm run dev` running.
