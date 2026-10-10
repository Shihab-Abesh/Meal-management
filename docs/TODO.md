# Implementation To-Do List

Tracks every milestone in [SPEC.md](SPEC.md). Each milestone ends with the same verification gate before the next one starts.

**Verification gate (run after every milestone):** migrations · seeders · `php artisan test` · `npm run build` · route list check · validation · authorization · responsive check (320/375/390/430 px, tablet, laptop, desktop) · regression of earlier milestones · completion report below.

Legend: `[x]` done and verified · `[ ]` not done

---

## Milestone 01 — Foundation + Authentication + Vendor Profile

- [x] Login with email or phone, logout, remember me
- [x] Forgot / reset password (enumeration-safe, throttled)
- [x] Session regeneration, inactive-user sign-out, login rate limiting
- [x] Roles: Vendor Admin, Vendor Staff, Client Admin, Rider (`UserRole`)
- [x] Permission gates (`Permission` enum registered as Gates)
- [x] Vendor profile (all fields incl. logo upload, VAT/TIN, status) — view + edit
- [x] Dashboard shell with Total Clients, Total Employees, Today's Meals, Today's Revenue, Pending Deliveries cards
- [x] App layout: desktop sidebar, top header, mobile drawer, bottom tab bar, profile menu
- [x] Own profile + password change
- [x] Tables: users (soft deletes, unique email/phone, indexes), vendor_profiles
- [x] Tests: auth, password reset, profile, vendor profile, dashboard role cards, shared props
- [x] Verification gate

## Milestone 02 — Client / Organization Management

- [x] `clients` table (all spec fields, soft deletes, unique name, indexes)
- [x] Extend permissions: operational (staff default, revocable) vs critical (admin default)
- [x] `users.client_id` for client accounts (client users belong to one client)
- [x] Client list: search, filter (status, city, meal type), sort, pagination; table on desktop, cards on mobile
- [x] Create / edit / view client; activate / deactivate; delete / restore
- [x] Client details: info, contact, employee count, today's meals, monthly meals, outstanding balance, recent orders, recent meal activity
- [x] Client users: create / edit / activate / deactivate / reset password (vendor admin, or staff with permission)
- [x] Client isolation enforced server-side (policies + scoped queries; never trust client_id from requests)
- [x] Reusable components: Modal, Confirmation Dialog, Pagination, Search, Filter, Sortable table headers, Date picker, Loading state
- [x] Tests: CRUD, search, pagination, validation, client login, isolation, unauthorized access
- [x] Verification gate

## Milestone 03 — Employee Management

- [x] `employees` table (unique employee ID per client, soft deletes, indexes)
- [x] List, search, filter, pagination; add / edit / view; activate / deactivate
- [x] Employee history (meal history + activity)
- [x] CSV import with template download; result: successful / failed / duplicate / validation errors
- [x] Client admin manages only own employees; vendor manages all
- [x] Tests: CRUD, search, filter, import validation, authorization, isolation
- [x] Verification gate

## Milestone 04 — Menu + Pricing Management

- [x] `menu_items` (category, default price, status, optional image)
- [x] `client_menu_prices` (client × meal type price with effective-from date, price history)
- [x] `daily_menus` + `daily_menu_items` (date × meal type, items, price)
- [x] Menu item CRUD, enable / disable
- [x] Client-specific pricing UI with history
- [x] Daily menu create / edit, history, duplicate previous menu
- [x] Price resolution service: client price → daily menu price → default setting
- [x] Today's Menu page for clients
- [x] Tests: price resolution, different client prices, price changes over time, daily menu, authorization
- [x] Verification gate

## Milestone 05 — Daily Meal Management

- [x] `meal_entries` (unique employee/date/meal type, unit price snapshot, status)
- [x] Meal entry screen: date, client, meal type; large YES/NO touch targets; sticky summary bar
- [x] Bulk actions: select all, unselect all, mark all lunch, mark all dinner, clear all
- [x] Live calculation: lunch qty, dinner qty, total meals, total amount
- [x] Cutoff enforcement (client cutoff → default setting); vendor admin override
- [x] Confirm meals in a transaction; duplicate prevention; editing
- [x] Meal calendar (month view of daily activity)
- [x] Meal history with filters (client, employee, date, meal type, status)
- [x] Tests: entry, bulk, cutoff, override, price calculation, duplicates, editing, history, isolation
- [x] Verification gate

## Milestone 06 — Order + Rider + Delivery Management

- [x] `orders`, `order_items`, `riders`, `deliveries`
- [x] Automatic order generation / sync on meal confirmation (transaction)
- [x] Order list, filters, details, status lifecycle with allowed transitions, cancel
- [x] Rider CRUD, search, activate / deactivate, details, delivery history, optional login account
- [x] Rider assignment (transaction), delivery board
- [x] Rider panel: today's deliveries, details, status buttons (Picked Up → On The Way → Delivered), history
- [x] Rider never sees amounts, other clients, settings or employee data
- [x] Tests: full lifecycle, invalid transitions, rider authorization, order totals
- [x] Verification gate

## Milestone 07 — Billing + Payments

- [x] `bills`, `payments`
- [x] Generate / recalculate bill for client + period from confirmed meals
- [x] Payments (cash, bank, bKash, Nagad, other) in a transaction; paid / due / status
- [x] Overdue handling (due date + scheduled command)
- [x] Client billing view: current balance, paid, due, payment history, monthly statement
- [x] Tests: full, partial, multiple payments, due calculation, billing period, isolation
- [x] Verification gate

## Milestone 08 — Reporting + Analytics

- [x] Daily report (totals + client-wise table)
- [x] Monthly report (totals + client-wise breakdown)
- [x] Client report, employee report, menu report, rider report
- [x] CSV (Excel-friendly) and PDF export
- [x] Charts: daily meal trend, monthly trend, client revenue distribution
- [x] Live dashboard figures
- [x] Tests: report figures checked against database records, export responses, authorization
- [x] Verification gate

## Milestone 09 — Settings + Activity Log + System Polish

- [x] `settings` (meal, cutoff, default pricing, billing, notification settings)
- [x] Vendor staff user management with configurable permissions
- [x] `activity_logs` for all listed events (user, action, module, record, timestamp, IP)
- [x] Activity log viewer with filters
- [x] Notifications honour notification settings
- [x] Security review: authz, CSRF, validation, mass assignment, SQL injection, XSS, uploads, rate limiting, sessions
- [x] Performance review: N+1, eager loading, indexes, pagination, heavy reports, payload size
- [x] Responsive audit 320 / 375 / 390 / 430 / tablet / laptop / desktop
- [x] Verification gate

## Milestone 10 — Final QA + Production Readiness

- [x] Functional QA of every module
- [x] Authorization QA per role; explicit client A ↔ client B and rider isolation tests
- [x] Calculation QA (meal count, price, order total, monthly total, paid, due)
- [x] Friendly 403 / 404 / 419 / 422 / 429 / 500 handling
- [x] Empty states and loading states everywhere; no duplicate submissions
- [x] Cleanup: debug code, console logs, unused imports / components, dead code
- [x] Tests, build, migrations on MySQL, production configuration review
- [x] Final report

---

## Completion reports

### Milestone 01 — Foundation + Authentication + Vendor Profile

Already implemented before this session; re-verified on 2026-10-06.

- **Completed:** authentication (email/phone login, logout, remember me, forgot/reset password, rate limiting, inactive-account sign-out), four roles with permission gates, vendor profile (view/edit, logo upload), dashboard shell with role-specific cards, responsive layout (sidebar, header, drawer, bottom tab bar, profile menu), own-profile and password pages.
- **Database:** `users` (unique email/phone, role/status index, soft deletes, JSON permissions), `vendor_profiles`, sessions, cache, jobs.
- **Tests:** 63 passing (auth, password reset, profile, vendor profile, dashboard, shared data, permissions).
- **Build:** `npm run build` succeeds.
- **Known issues:** none.

### Milestone 02 — Client / Organization Management

- **Completed:** client list (search, status/meal-type/city filters, sortable columns, pagination; cards on mobile, table on desktop), add/edit/view, activate/deactivate, archive/restore, client detail page (contact, meal settings, figures, client logins, recent activity), client login management (create, edit, reset password, deactivate, remove), sign-in blocked and sessions ended when a client is deactivated or archived, activity logging foundation (sign-ins and client changes), configurable staff permissions (operational defaults, revocable; critical permissions admin-only unless granted).
- **Components added:** Modal (bottom sheet on phones), ConfirmDialog, Pagination, SearchInput, FilterPanel (inline / bottom sheet), sortable DataTable with row actions and tappable mobile cards, Toggle, ChoiceChips, DateInput, LoadingState/Skeleton, ActionMenu, FormActions, `useFilters` and `useMediaQuery` hooks.
- **Database:** `clients` (unique name, city/status indexes, soft deletes, `created_by` FK), `users.client_id` FK (restrict on delete), `activity_logs` (user, module, action, subject morph, client, properties, IP; indexed by module/client/date).
- **Design note:** client users are `users` rows with role `client_admin` and `client_id`; a separate `client_users` table would only duplicate the users table.
- **Tests:** 97 passing (34 new: CRUD, search, filters, sorting, pagination, validation, archive/restore, staff permissions, client/rider refusal, client login lifecycle, scoped bindings, cross-client protection, inactive-client sign-out, login logging).
- **Responsive:** 70 page × viewport checks (320–1440 px), no horizontal overflow, no console errors; screenshots reviewed at 375 px and 1440 px.
- **Known issues:** none. Employee, meal, order and balance figures on the client page are wired in later milestones.

### Milestone 03 — Employee Management

- **Completed:** employee list (search, client/status/department/meal/preference filters, sorting, pagination; responsive columns on desktop, cards on mobile), add (with "save & add another"), edit, view, activate/deactivate, employee history (record changes; meal history is added in Milestone 05), CSV import with template download, header aliases, semicolon/BOM handling, per-row validation and a result report (imported / duplicates / failed with reasons). Employee counts now appear on the client list, client page and both dashboards.
- **Isolation:** one set of routes for vendor users and client admins; client admins are scoped by `Employee::visibleTo()` and `EmployeePolicy`, their `client_id` is forced server-side on create and import, and an employee can never be moved to another client.
- **Database:** `employees` (FK to clients, unique `client_id + employee_code`, indexes on client/status/name and client/department). Employees are deactivated, not deleted, so meal history stays intact.
- **Tests:** 121 passing (24 new: CRUD, filters, pagination, per-client ID uniqueness, validation, client-admin isolation and forced client, refusal for riders/staff without permission, dashboard counts, import success/validation/duplicates/file errors/isolation).
- **Responsive:** 98 page × viewport checks clean after fixing two issues the audit found (employee/client tables too wide at 768–1024 px → secondary columns now appear progressively; a hidden button overriding `hidden` on phones).
- **Known issues:** none.

### Milestone 04 — Menu + Pricing Management

- **Completed:** menu items (add/edit in a modal with optional photo, enable/disable, delete only if never used, category/status filters, usage count), daily menus (one per date × meal type, ordered item picker grouped by category with search, optional menu price, notes, month-by-month history, "copy previous menu" on the form, "copy to another day" from the history), client-specific pricing (per client × meal type with an effective-from date, full history, scheduled future prices that can be removed, past prices immutable), default meal prices, Today's Menu page (date navigation; client accounts see the price they pay), current prices on the client detail page.
- **Price rule (`PricingService`):** client price in effect on the date → that day's menu price → default setting. Money uses exact decimal arithmetic (`App\Support\Money`, brick/math) and `DECIMAL(10,2)` columns.
- **Access:** menu pages need `menu.manage`; pricing needs `pricing.manage` (admin-only by default); staff without pricing access cannot change a menu's price; vendor staff without financial access see menus without prices; riders have no menu access.
- **Database:** `settings` (key/value JSON, falls back to `config/catering.php`), `menu_items` (unique name, category/status indexes), `client_menu_prices` (unique client + meal + effective date), `daily_menus` (unique date + meal), `daily_menu_items` (unique menu + item, sort order). A `DateOnly` cast stores dates as `YYYY-MM-DD` so date comparisons behave identically on MySQL and SQLite and stay index-friendly.
- **Bugs found and fixed during verification:** settings defaults were read through dotted `config()` keys and came back empty; the daily-menu uniqueness rule did not ignore the menu being edited (resource parameter is `daily_menu`); `firstOrNew()` tripped the mass-assignment guard on `client_id`; menu history listed dinner before lunch.
- **Tests:** 150 passing (29 new: price precedence, per-client prices, price changes by effective date, exact money arithmetic, set/replace/remove prices, defaults, access rules, menu item CRUD/validation/image/delete protection, daily menu create/unique/update/duplicate/previous/history, Today's Menu prices per client and per role).
- **Responsive / browser:** 77 page × viewport checks clean after narrowing the menu item table at 1024 px; the create-menu flow was driven end-to-end in a browser.
- **Known issues:** none.

### Milestone 05 — Daily Meal Management (core)

- **Completed:** vendor day overview (every active client's lunch/dinner counts with cutoff state; tap to open), meal sheet per client and date (large YES/NO toggles per meal, whole-row toggle when one meal is in focus, search, "All meals / Lunch / Dinner" focus, bulk actions Select all · Unselect all · All lunch · All dinner · Clear all, sticky live summary with quantities, amount and `qty × price` breakdown, unsaved-changes guard), confirmation in a transaction with a per-client row lock, cutoff enforcement per meal (client cutoff → default), vendor-admin override with explicit confirmation (logged with `override: true`), meal calendar (month grid, per-day lunch/dinner counts), meal history (date range, client, employee, meal, status filters with confirmed-meal and amount totals), employee page meal history, live Today's Meals / Today's Revenue / This Month figures on dashboards and the client page.
- **Rules enforced server-side:** no duplicate meal per employee/date/meal (unique index + upsert logic); employee IDs must belong to the client and new selections must be active; client accounts always save for their own client; only meal types the client receives; up to 60 days ahead; past dates and passed cutoffs are closed; unit price is snapshotted at confirmation so later price changes never alter recorded meals; staff without financial access never receive amounts.
- **Database:** `meal_entries` (FKs to clients/employees/users, unique employee + date + meal, indexes on client/date/meal/status and date/status).
- **Bugs found and fixed during verification:** `pluck()` keyed by an enum-cast column crashed the overview and calendar (aggregates now read raw rows); the `distinct` rule compared employee IDs across lunch and dinner and rejected employees taking both meals.
- **Tests:** 173 passing (23 new: overview counts, sheet props, confirmation with client prices, 22 × ৳120 = ৳2,640 and dashboard revenue, editing/cancel/restore without duplicates, idempotent re-save, DB-level duplicate refusal, price snapshot after a price change, cutoff for client admins, staff refusal and admin override with confirmation, granted override permission, past/future/too-far dates, default cutoff, cross-client and inactive employees, unserved meal types, forced client for client accounts, permission/role refusal, inactive client, history filters and isolation, calendar counts, amount hiding).
- **Browser checks:** client admin on a 375 px phone (bulk + individual toggles, live total ৳1,100 = 8 × ৳120 + 1 × ৳140, matches the database), vendor admin override of a closed day; 98 page × viewport checks clean.
- **Known issues:** none. Orders are generated from confirmed meals in Milestone 06.

### Milestone 06 — Order + Rider + Delivery Management

- **Completed:** orders generated inside the meal-confirmation transaction (one per client × date × meal; vendor confirmations start Confirmed, client confirmations Pending; quantity and amount always equal the confirmed meals at their snapshotted prices; removing every meal cancels the order and adding meals back restores it; dishes copied from the day's menu), order list (date navigator, status chips, client/meal/status filters, order-number search), order page (progress bar, next-step buttons, cancel with reason, editable delivery address/time/notes until delivered, history), delivery board (To assign / Assigned / On the way / Delivered tabs, assign or change rider with time and note, unassign), rider management (list with search and filters, add/edit with optional phone login, activate/deactivate, detail page with delivery counts and history), rider app (today's deliveries with open ones first, delivery page with map and call buttons, one large next-step button, history), live dashboard panels (vendor: today's deliveries; client: today's meals with order progress; rider: link to deliveries), recent orders on the client page, demo riders and orders.
- **Lifecycle:** Pending → Confirmed → Preparing → Ready → Assigned → Picked Up → On The Way → Delivered, or Cancelled before delivery. Allowed transitions live in `OrderStatus`; every change runs in a transaction on a locked order row and is logged.
- **Access:** orders need `orders.manage`, the board and rider steps `deliveries.manage`, rider management `riders.manage`; riders reach only their own deliveries (403 otherwise) and never receive amounts, other clients or employee data.
- **Database:** `riders` (unique phone, optional unique `user_id`), `orders` (unique order number, unique client + date + meal, indexes on date/status), `order_items`, `deliveries` (unique order, rider + assigned_at index).
- **Bugs found and fixed during verification:** the order-number search did not escape `%`/`_`; item quantities went stale when meals changed after dispatch; delivered or cancelled orders could still have their delivery details edited; three layout overflows (delivery tabs scrolled sideways on phones, dashboard and rider tables too wide at 1024 px, rider page grid widened by its content at 320 px).
- **Tests:** 214 passing (41 new: order generation and totals, client vs vendor start status, edit/cancel/restore, menu items, price snapshots, amounts hidden from staff; full lifecycle with timestamps, invalid transitions, rider change until pickup, inactive riders, unassign, cancellation cancels meals, permission split between orders and deliveries, client/rider refusal; rider CRUD, validation, login by phone, login follows edits, deactivation signs out, detail counts; rider panel isolation, no amounts, step order, history, dashboard counts).
- **Browser checks:** end-to-end assign (desktop) → picked up → on the way → delivered (375 px phone) with no console errors; 140 page × viewport checks clean (320–1440 px).
- **Known issues:** none.

### Milestone 07 — Billing + Payments

- **Completed:** bill generation for a client and period (month picker or custom dates, live server-side preview of meals and amount, overlap warning), bill totals = confirmed meals × the price each meal was confirmed at (one line per meal type and unit price, so a mid-month price change shows as two lines), bill page (total / paid / due, lines, payments, recalculate when meals changed, delete while no payment was ever recorded, print), payments (cash, bank, bKash, Nagad, other; reference and note; partial and multiple payments; amount may not exceed what is due; removal keeps a soft-deleted record), payments list with totals per method, bill list with billed/paid/due totals and client/status/month filters, monthly statement (day-by-day meals and amount, bills covering the month, payments made in it, current balance) for vendors and clients, client billing page (current balance with overdue amount, paid, billed, meals not billed yet, bills, payment history), Balance Due card on the client dashboard, outstanding balance and billing links on the client page, emails to client accounts when a bill is issued, a payment is received or an order is delivered (each switchable in settings; failures never block the action), demo bills and payments.
- **Rules:** bill periods of a client never overlap, so no meal is billed twice; a bill covers at most 92 days; status is Paid when nothing is due, Overdue when money is owed after the due date (worked out live and stored nightly by `bills:mark-overdue`, scheduled at 00:15), otherwise Partially Paid or Unpaid; generation, payments, removals and recalculation run in transactions with row locks and are logged.
- **Access:** vendor users need `financials.view` or `billing.manage` to see billing and `billing.manage` to change it (admin-only by default); client accounts see only their own bills, statement and payments, never who recorded them.
- **Database:** `bills` (unique bill number, unique client + period start, indexes on client/period end, status/due date), `payments` (FKs to bill and client, soft deletes, indexes on client/date and date). Amounts are `DECIMAL(12,2)` and calculated with exact decimal arithmetic.
- **Bugs found and fixed during verification:** deleting a bill whose payments had been removed would have hit the payment foreign key (deletion is now refused once any payment was recorded); bill and statement tables overflowed at 320 px and the client bill table at 1024 px.
- **Tests:** 236 passing (22 new: 1,200 meals × ৳120 = ৳144,000, per-client prices, mid-period price change, only confirmed in-period meals of the client, overlap/empty/too-long/reversed periods, full, partial and multiple payments, payment validation, payment removal, overdue status and nightly job, recalculation keeping payments and refusing a total below paid, deletion rules, preview, list totals and filters, emails on/off, permission matrix; client balance and unbilled meals, own bill only, statement always own organization, vendor statement access, dashboard and client page balances, page restricted to client accounts).
- **Browser checks:** bill generated from the preview and a partial bKash payment recorded (desktop); client billing and statement on a 375 px phone; 112 page × viewport checks clean (320–1440 px).
- **Known issues:** none. The scheduler must be run every minute in production (`php artisan schedule:run`) for overdue statuses to be stored; screens already show overdue bills correctly without it.

### Milestone 08 — Reporting + Analytics

- **Completed:** one Reports page with Daily (clients served, employees, lunch, dinner, total meals, revenue, pending orders, completed deliveries; client-wise Lunch / Dinner / Total / Amount), Monthly (meals, lunch, dinner, revenue, paid, due; client-wise breakdown), Client (meals, revenue, paid in the period, due today; day-by-day history), Employee (lunch, dinner, total and amount per employee, one client or all), Menu (times on menu, lunch/dinner split, meals served with each item, first and last served) and Rider (assigned, completed, cancelled, pending, meals delivered) reports. Choosing the report and date, month or range updates it immediately (with This month / Last month shortcuts); every report downloads as Excel-friendly CSV (UTF-8 BOM, formulas neutralised) or PDF (A4, landscape for wide reports, embedded font subset). Charts where they help: daily lunch/dinner columns and a 12-month trend on the monthly report, revenue (or meals) by client as ranked bars, daily columns on the client report, and a 14-day meal trend on the vendor dashboard. Client accounts get their own Meal summary and Employee report.
- **Charts:** plain React/CSS (no chart library): one validated two-colour series palette (CVD ΔE 24.7, normal ΔE 33.6, ≥ 3:1 on white), columns ≤ 24 px with 4 px rounded caps and 2 px gaps between stacked segments, clean axis ticks, a legend for two series, a sparing value label, hover and arrow-key tooltips listing every series; the same figures are always in the table below.
- **Access:** vendor users need `reports.view`; amounts are removed from figures, columns, charts and exports without `financials.view`; client accounts only get their own organization whatever client is requested; ranges are limited to one year; exports are throttled and logged.
- **Package added:** `barryvdh/laravel-dompdf` (PDF export).
- **Bugs found and fixed during verification:** `ReportRequest::date()` clashed with Laravel's `Request::date()`; PDF number headers were left-aligned and files embedded the whole font (882 KB → 27 KB); colliding last axis labels; month picker squeezed on phones (the "This month"/"Today" shortcut is now an icon there); the 8-column menu table was too wide at 1024 px; the dashboard deliveries card stretched to the chart column's height.
- **Tests:** 248 passing (12 new: daily, monthly, client, employee, menu and rider figures checked against the records they come from, cancelled meals excluded, staff without financial access get no amounts (screen and CSV), client accounts limited to their own organization and allowed reports, CSV content and formula neutralising, PDF response, access and range validation).
- **Browser checks:** 98 page × viewport checks clean (320–1440 px); monthly report and dashboard reviewed at 1280 px and 375 px.
- **Known issues:** none. PDFs show amounts with the currency code (BDT) because the PDF font has no ৳ glyph.

### Milestone 09 — Settings + Activity Log + System Polish

- **Completed:** Settings page with sections saved independently (default lunch/dinner cutoff times, default meal prices — also needs the pricing permission —, payment terms in days, email notifications for new bills, payments received and deliveries, with a notice while mail only goes to the log) and shortcuts to the vendor profile and users; Users page for vendor admins and staff (search and filters, add/edit with email or phone sign-in, password reset, role, activate/deactivate, per-permission checkboxes grouped into operational and critical; staff get exactly what is ticked, admins always everything; nobody can change their own role or deactivate themselves and there is always at least one active admin; deactivated users are signed out on their next request); Activity log viewer (date range, search, module, user and client filters, record, IP and stored old/new values on demand). Newly logged: sign-out, own profile and password changes, vendor profile changes, user management, report exports.
- **Security review:** every route sits behind `auth` plus a role check, with permissions enforced by `can:` middleware, policies or Form Request `authorize()`; client accounts are scoped server-side whatever IDs are sent; CSRF is on for all web routes (Inertia sends the XSRF token); validation through Form Requests or validated arrays only, no `$request->all()`; mass assignment limited by `$fillable` / service-built attributes, `forceFill` only with server-decided values; no raw SQL with user input (LIKE wildcards escaped, sort columns allow-listed); no `dangerouslySetInnerHTML`/`{!! !!}`; CSV exports neutralise spreadsheet formulas; uploads limited by type and size; rate limits on sign-in, password reset, imports, meal saves, rider steps, bills, payments and exports; sessions regenerated on sign-in and invalidated on sign-out, HTTP-only and SameSite=Lax cookies; new `SecurityHeaders` middleware (nosniff, SAMEORIGIN framing, strict referrer policy, permissions policy, HSTS over HTTPS).
- **Performance review:** strict mode outside production turns any lazy load (N+1) into an error, and the whole suite runs under it; lists are paginated; reports, statements and dashboards aggregate in SQL; the hot filters have matching indexes (meal date/status, client/date/meal, order date/status, bill status/due date, payment client/date, activity module/date and client/date); settings are read once per request; Inertia props only carry what each page shows.
- **Responsive audit:** 448 page × viewport checks across all four roles (64 pages at 320, 375, 390, 430, 768, 1024 and 1440 px): no horizontal overflow, no console errors, no failed requests.
- **Tests:** 270 passing (22 new: settings sections, validation, permission split, defaults used by cutoffs and billing; staff accounts with exact permissions, revoking operational permissions, admins always full, validation, self-lockout and last-admin protection, sign-out on deactivation, password reset, access; every listed event logged with user and IP; viewer filters; viewer access; security headers; friendly 403/404/500 pages, expired form, JSON errors).
- **Known issues:** none.

### Milestone 10 — Final QA + Production Readiness

- **Functional QA:** every module exercised by the feature suite and in the browser: sign-in by email and phone, dashboards per role, clients and client logins, employees and CSV import, menu, pricing, daily meal sheet with cutoff and override, orders and the full delivery lifecycle (assigned on the board, delivered from a rider's phone), bills, partial bKash payment, client billing and statement, all six reports with CSV and PDF, settings, users, activity log.
- **Authorization QA:** vendor admin full access; staff limited to their permissions (and amounts hidden without financial access); client A never sees client B (employees, meals, bills, payments, statements, reports — even when B's ID is sent); riders only their own deliveries and never amounts; covered by explicit tests in each module.
- **Calculation QA:** on the MySQL demo data all 110 orders equal their confirmed meals (count and amount), all bills equal their meals, payments and lines (paid = sum of payments, due = total − paid), and the monthly report equals the raw meal records; tests cover 22 × ৳120 = ৳2,640 and 1,200 × ৳120 = ৳144,000.
- **Error handling:** friendly pages for 403, 404, 429, 503 and (with debug off) 500; an expired (419) or throttled form goes back with a message instead of losing the page; 422 shows inline field errors; JSON requests keep plain errors.
- **Empty and loading states:** every list has an empty state (with a first-action button where it helps); filters and reports keep the previous view dimmed while loading; every submit button shows a spinner and is disabled while sending, so nothing is submitted twice.
- **Cleanup:** no debug calls, console logs or TODOs; Pint clean (unused PHP imports removed); no unused JS imports; the only unused component (LoadingState) is now used; the skeleton's sample console command replaced by the overdue job; temporary data created during browser testing removed.
- **Verified:** 270 tests on SQLite and the same 270 on MariaDB 10.4 (`meal_management_testing`); all 13 migrations roll back and re-run cleanly on MySQL; `npm run build` succeeds; README updated with features, demo accounts, scheduler, roles and a production checklist; `.env.example` notes the secure session cookie.
- **Known issues:** none. Before going live, follow the production checklist in the README (debug off, HTTPS, secure cookies, real mail settings, scheduler, backups).

---

## After the milestones

### Vendor notifications and menu chart (requested 2026-10-07)

- **Notifications:** a bell in the top bar for vendor users with an unread badge (and the count in the tab title), the newest alerts, mark all as read and a link to a full Notifications page (all/unread, by category, grouped by day, mark read/unread, clear read). Alerts: a client places, changes or cancels an order (from the meal sheet), an order is delivered, bills become overdue (nightly job). They go to active vendor users who can act on them (orders, deliveries or billing access), never to the person who caused the event. While an order alert is unread, later changes fold into it ("24 → 28 meals"; a change back to what the vendor knew removes it), so a client editing the sheet repeatedly produces one alert. The bell checks for news every 20 seconds (60 in a background tab), then shows a toast with a View link, plays an optional chime and, in a background tab, a desktop alert. Each user chooses which categories they get; sound and desktop alerts are per device. Opening an alert marks it read and goes to the order or bills. Stored with Laravel's database notifications (`notifications` table, new `users.notification_preferences`); alert ids are time-ordered.
- **Menu chart:** Daily Menus became a menu chart: a week (Saturday to Friday) with lunch and dinner rows on wide screens and one card per day on phones, or a month calendar (dish names on wide screens, planned-meal dots on phones). Each dish shows a category colour; a progress bar counts planned meals. Tapping a meal opens an editor in place: dishes grouped by category with search, serving order (move up/down), "Same as <last time>?" one-tap copy, price (with the pricing permission) and note, copy to another day, delete. "Copy last week" fills only the empty slots of the shown week. The separate create/edit pages were removed; the chart loads one week or month at a time.
- **Tests:** 284 passing on SQLite and on MariaDB (14 new notification tests: who is alerted, folding of order changes, cancellations, preferences, feed, open/read/clear, isolation between users, deliveries, overdue bills, access; menu chart week/month ranges, previous menu, copy last week).
- **Browser checks:** a client confirmed meals on a phone while the admin's dashboard showed the toast and badge 13–14 seconds later without reloading; opening it went to the order and cleared the badge; the menu chart was planned and a week copied on a phone; 98 page × viewport checks clean.
- **Bugs found and fixed during verification:** the bell kept a stale count after opening an alert; the dropdown stretched across the page (Headless UI's inline max-width); toolbar and test-button overflows at 768–1024 px; alerts created in the same second could list in random order on MySQL.
