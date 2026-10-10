# Office Meal & Catering Management System — Specification

Project brief as provided by the product owner. Development follows the milestones below, one at a time, each requiring explicit sign-off (`SIGN-OFF MILESTONE X`) before the next starts.

## 1. Project overview

Build a production-ready **Single Vendor** Office Meal & Catering Management System. This is **not** a SaaS application.

One catering vendor uses the system to manage multiple client organizations/offices, their employees, daily meal requirements, menus, orders, riders, deliveries, billing and reports. It replaces the vendor's manual notebook-based meal counting and reporting.

The app must be fully responsive, mobile-first, fast, clean, easy to use, professional, production-ready, touch-friendly, accessible and secure, and work on desktop, laptop, tablet and mobile. The mobile experience is especially important: it should feel like a modern mobile app.

## 2. Required technology stack

Laravel, PHP, MySQL, Inertia.js, React, Tailwind CSS. Use the existing project structure and installed packages where possible.

Do not introduce unnecessary frameworks. Do not replace Inertia/React with Blade UI. No Vue. No MongoDB. No SaaS architecture.

## 3. Development rule

Develop milestone by milestone; complete only one at a time. After each milestone:

1. Run migrations, seeders if required, backend tests, frontend/build checks.
2. Check all routes, validation, authorization, responsive behaviour, mobile UI and desktop UI.
3. Fix all errors and verify existing functionality still works.
4. Provide a concise milestone completion report, then **STOP**.

Do not start the next milestone until the user writes `SIGN-OFF MILESTONE X`.

## 4. Architecture

Single vendor, multiple client organizations (e.g. vendor "ABC Catering" with clients "XYZ Software Ltd.", "ABC Bank Ltd.", "Tech Solutions Ltd.", "Digital Bangladesh Ltd.").

Each client has its own employees, meal entries, meal history, reports and user accounts. A client must **never** access another client's data. Authorization is enforced in the backend, never only by hiding things in the frontend.

## 5. User roles

- **Vendor Admin** — full access: Dashboard, Clients, Client Users, Employees, Meals, Menu, Orders, Riders, Deliveries, Reports, Billing, Payments, Settings, Activity Logs.
- **Vendor Staff** — limited vendor-side access to configurable operational modules (Clients, Employees, Meals, Orders, Riders, Deliveries, Reports). Cannot modify critical system settings unless permission is explicitly granted.
- **Client Admin** — only their own organization: Dashboard, Employees, Daily Meal Entry, Meal History, Today's Menu, Reports, Profile.
- **Rider** — only assigned deliveries: Today's Deliveries, Delivery Details, Delivery Status, Delivery History. Must never see vendor financial information or other clients' data.

## 6. Core modules

Authentication · Vendor Dashboard · Client/Organization Management · Client User Management · Employee Management · Meal Management · Menu Management · Pricing Management · Order Management · Rider Management · Delivery Management · Billing & Payment Management · Daily / Monthly / Employee / Client / Menu Reports · Vendor Settings · Activity Logs · Responsive UI.

---

## Milestone 01 — Foundation + Authentication + Vendor Profile

**Implement:** login, logout, forgot password, authenticated session, user roles, vendor profile, basic dashboard shell, sidebar, header, responsive navigation, mobile navigation, profile menu.

**Vendor profile fields:** Business Name, Organization Name, Owner Name, Phone, Email, Address, City, Logo, Business Type, VAT/TIN (optional), Status, Created At.

**Dashboard:** placeholder cards for Total Clients, Total Employees, Today's Meals, Today's Revenue, Pending Deliveries. No advanced reports yet.

**UI:** desktop — sidebar, top header, content area. Mobile — collapsible drawer, bottom-friendly controls, touch-friendly buttons, responsive cards and tables. Avoid horizontal scrolling.

**Database:** foundational tables with foreign keys, indexes, unique constraints, timestamps, soft deletes where appropriate.

**Verify:** login, logout, authentication, role protection, vendor profile, responsive navigation, mobile and desktop layout, migrations, validation.

**Report:** what was implemented, files/modules changed, database changes, tests performed, remaining issues. Then STOP.

## Milestone 02 — Client / Organization Management

**Client fields:** Organization Name, Contact Person, Phone, Email, Address, City, Delivery Address, Number of Employees, Meal Types, Office Start Time, Lunch Cutoff Time, Dinner Cutoff Time, Special Instructions, Status, Notes.

**Features:** client list with search, filter, sort, pagination; add, edit, view; activate/deactivate; delete/restore where appropriate.

**Client details page:** organization info, contact info, employee count, today's meal count, monthly meal count, outstanding balance, recent orders, recent meal activity.

**Client login:** Vendor Admin creates client users (Name, Email/Phone, Password, Client Organization, Status). Client users only access their own organization.

**Authorization:** organization-level authorization in Laravel. Never trust `client_id` from frontend requests.

**UI:** mobile — cards, compact info, action menu, bottom sheets/modals. Desktop — professional data table.

**Verify:** create, edit, delete/deactivate client, search, pagination, client login, client isolation, unauthorized access, mobile UI. STOP.

## Milestone 03 — Employee Management

**Employee fields:** Employee Name, Employee ID, Phone, Email, Department, Designation, Meal Preference, Lunch Enabled, Dinner Enabled, Status, Joining Date, Notes.

**Features:** list, add, edit, view, activate/deactivate, search, filter, pagination, employee history.

**Bulk import:** CSV/Excel-friendly architecture; at minimum structured CSV. Validate duplicate employee ID, invalid phone, missing name, invalid meal preference. Show result: successful, failed, duplicate, validation errors.

**Isolation:** Client Admin manages only their organization's employees; vendor manages all.

**Verify:** CRUD, search, filter, import, validation, authorization, mobile layout. STOP.

## Milestone 04 — Menu + Pricing Management

**Menu items:** Item Name, Category, Description, Default Price, Status, Image (optional). Examples: Rice, Chicken Curry, Egg, Vegetable, Dal, Biryani.

**Categories:** Main Course, Side Dish, Protein, Beverage, Dessert, Other.

**Client-specific pricing:** never assume one price for all clients (e.g. Client A lunch ৳120, Client B ৳130, Client C ৳150).

**Daily menu:** menu for a specific date per meal type (e.g. 06 October 2026, Lunch: Rice, Chicken Curry, Dal, Vegetable — ৳120).

**Features:** menu item CRUD, pricing, client-specific price, daily menu, menu history, duplicate previous day's menu, enable/disable items.

**Verify:** pricing calculation, different client prices, daily menu, menu history, price changes, authorization, responsive UI. STOP.

## Milestone 05 — Daily Meal Management (core module)

Replaces the notebook meal count.

**Daily meal entry:** select Date, Client, Meal Type (Lunch / Dinner); list employees with simple YES/NO (or Present / Not Required) per meal.

**Bulk actions:** Select All, Unselect All, Mark All Lunch, Mark All Dinner, Clear All.

**Automatic calculation:** lunch quantity, dinner quantity, total meals, total amount (e.g. 22 lunch × ৳120 = ৳2,640).

**Cutoff:** lunch cutoff (e.g. 10:00 AM), dinner cutoff (e.g. 04:00 PM). After cutoff normal users cannot modify; authorized Vendor Admin can override.

**Meal calendar:** daily meal activity by date. **Meal history:** filter by client, employee, date, meal type, status.

**Mobile-first:** extremely easy on mobile; employees as compact cards/rows with large touch targets — not a tiny checkbox table.

**Verify:** daily entry, bulk selection, lunch, dinner, cutoff, override, price calculation, duplicate prevention, editing, history, mobile usability. STOP.

## Milestone 06 — Order + Rider + Delivery Management

**Order generation:** confirmed meals automatically generate daily order data (e.g. ABC Ltd., Lunch, 22 meals, ৳120, total ৳2,640).

**Order fields:** Order Number, Client, Date, Meal Type, Quantity, Amount, Status, Rider, Delivery Address, Notes.

**Statuses:** Pending, Confirmed, Preparing, Ready, Assigned, Picked Up, On The Way, Delivered, Cancelled.

**Rider fields:** Rider Name, Phone, NID (optional), Address, Emergency Contact, Vehicle Type, Vehicle Number, Joining Date, Status, Notes.

**Rider features:** add, edit, activate/deactivate, search, details, delivery history. **Assignment:** assign rider to a delivery (e.g. ABC Ltd, 35 meals, 11:45 AM, Rider: Rahim).

**Rider panel:** today's deliveries, delivery details, client name, delivery address, meal quantity, meal type, delivery status. Must not see other clients, revenue, profit, vendor settings or private employee information.

**Verify:** full lifecycle Pending → Confirmed → Preparing → Ready → Assigned → Picked Up → On The Way → Delivered; rider authorization. STOP.

## Milestone 07 — Billing + Payments

**Billing:** Total Meals × Client-specific Price = Total Amount (e.g. 1,200 × ৳120 = ৳144,000).

**Bill fields:** Client, Billing Period, Total Meals, Total Amount, Paid Amount, Due Amount, Status (Unpaid, Partially Paid, Paid, Overdue).

**Payment fields:** Date, Amount, Payment Method (Cash, Bank, bKash, Nagad, Other), Reference, Note.

**Client billing view:** current balance, paid, due, payment history, monthly statement.

**Verify:** full payment, partial payment, due calculation, multiple payments, billing period, client isolation. STOP.

## Milestone 08 — Reporting + Analytics

- **Daily report** (select date): total clients, total employees, lunch, dinner, total meals, total revenue, pending orders, completed deliveries; client-wise table (Client, Lunch, Dinner, Total, Amount).
- **Monthly report** (select month): total meals, lunch, dinner, revenue, paid, due, client-wise breakdown.
- **Client report:** total meals, revenue, paid, due, daily history.
- **Employee report:** employee, lunch count, dinner count, total meals, total amount.
- **Menu report:** menu item, usage count, date range.
- **Rider report:** rider, assigned, completed, cancelled, pending deliveries.
- **Export:** PDF; CSV/Excel where appropriate. One click: select date/month → Generate Report.
- **Charts** only where they help (daily meal trend, monthly meal trend, client revenue distribution). Don't overload the dashboard.

**Verify:** all report calculations against database records. STOP.

## Milestone 09 — Settings + Activity Log + System Polish

**Settings:** vendor profile, business information, meal settings, cutoff settings, default pricing, notification settings, user settings.

**Activity log** for: login, client created/updated, employee created, meal confirmed/edited, order created, rider assigned, delivery status changed, payment added, settings changed. Log user, action, module, record, timestamp, IP where appropriate.

**Security review:** authentication, authorization, CSRF, validation, mass assignment, SQL injection, XSS, file upload validation, rate limiting, session security.

**Performance:** N+1 queries, eager loading, indexes, pagination, heavy reports, Inertia payload size.

**Responsive audit:** 320, 375, 390, 430 px mobile, tablet, laptop, desktop — no major layout breaks. STOP.

## Milestone 10 — Final QA + Production Readiness

No new features; focus on stability, usability, security and correctness.

- **Functional QA:** authentication, vendor, clients, client login, employees, menu, pricing, daily meals, orders, riders, delivery, billing, payments, reports, settings.
- **Authorization QA:** Vendor Admin full access; Vendor Staff restricted; Client Admin own organization only; Rider assigned deliveries only.
- **Data isolation:** Client A ↔ Client B cannot see each other; rider cannot access vendor financial data.
- **Calculation QA:** meal count, price, order total, monthly total, paid, due.
- **Error pages:** friendly 404, 403, 419, 422, 429, 500.
- **Empty states** on every major list (e.g. "No employees found." / "Create your first employee.").
- **Loading states** for forms, tables, filters, reports, meal submission, order actions; prevent duplicate submissions.
- **Cleanup:** remove debug code, console logs, temporary test data, unused imports/components, dead code, development-only UI.
- **Run:** tests, build, migration verification, production configuration review.

---

## 7. UI / UX requirements

Modern, minimal, professional, clean, spacious, fast, mobile-first, touch-friendly — not an old-fashioned admin panel. Use Tailwind consistently.

**Mobile:** large touch targets, bottom-friendly actions, sticky action buttons where useful, cards instead of wide tables, responsive modal/drawer, compact navigation, easy date selection, easy YES/NO meal selection.

**Desktop:** sidebar, data tables, dashboard cards, filters, charts, multi-column layouts.

Desktop and mobile are the same system with responsive layouts, not two applications.

## 8. Component reusability

Reusable React components for: Button, Input, Select, Date Picker, Modal, Drawer, Dropdown, Table, Pagination, Badge, Card, Empty State, Loading State, Confirmation Dialog, Toast/Notification, Form Error, Search, Filter, Status Badge. Avoid duplicated UI code.

## 9. Database requirements

MySQL with proper relational design. Expected tables: users, vendor_profiles, clients, client_users, employees, menu_items, client_menu_prices, daily_menus, daily_menu_items, meal_entries, orders, order_items, riders, deliveries, bills, payments, activity_logs, settings. Additional tables only when technically necessary; no duplicate tables.

Use foreign keys, indexes, unique constraints, proper nullable fields, soft deletes where appropriate. Use transactions for meal confirmation, order generation, payment recording, billing calculation and rider assignment.

## 10. Business rules (mandatory)

1. One vendor only.
2. A client belongs to the vendor.
3. Employees belong to a client.
4. Client users belong to a client.
5. Client users can access only their own client data.
6. Meal entries belong to an employee and client.
7. A meal cannot be duplicated for the same employee/date/meal type.
8. Meal price is calculated using the applicable client pricing.
9. Orders reflect confirmed meal quantities.
10. Riders only access assigned deliveries.
11. Cutoff rules are enforced.
12. Authorized Vendor Admin can override cutoff restrictions.
13. All financial calculations use reliable decimal/database numeric types.
14. Critical operations use database transactions.

## 11. Main daily workflows

- **Vendor:** Login → Dashboard → Select Client → Today's Meal → See Employees → Mark YES/NO → Confirm Meals → System calculates quantity → System generates order → Assign Rider → Delivery → Delivered → Reports update automatically.
- **Client:** Login → Dashboard → Today's Meal → Select employees → Confirm → View Today's Menu → View Meal Summary → View Monthly Statement.
- **Rider:** Login → Today's Deliveries → Open Delivery → View Address → Picked Up → On The Way → Delivered.

## 12. Coding rules

Don't over-engineer or add unnecessary abstractions or packages. Follow Laravel conventions: Form Requests, Policies/Gates, Services for complex business logic, Eloquent relationships, database transactions, resource-style data preparation, proper validation, clean React components. Keep business logic out of React. Never trust client-provided IDs without authorization checks. Don't expose sensitive data in Inertia props.

## 13. Testing

Each milestone includes tests for at least: authentication, authorization, CRUD, validation, business rules, meal calculations, billing calculations, client data isolation. Add regression tests when fixing bugs.

## 14. Sign-off protocol

After each milestone, report in this shape, then write `STATUS: READY FOR SIGN-OFF` and stop:

```
Milestone Completed
Milestone X — NAME
Completed: …
Database Changes: migrations, relationships, indexes
Tests: …
Responsive Verification: mobile, tablet, desktop
Known Issues: none / list
```

If the user asks for changes, fix the current milestone first.

## 15. Final success criteria

Vendor manages the whole business and multiple clients; clients manage employees; daily meal entries work; vendor manages menus and prices; client-specific pricing works; daily meals are calculated automatically; orders are generated correctly; riders are assigned; delivery status is tracked; billing, payments and dues work; daily, monthly, employee and client reports work; PDF/CSV/Excel export works where implemented; role permissions work; client data is isolated; rider data is restricted; mobile and desktop responsive; validation and error handling complete; database relationships correct; critical calculations tested; no major console or backend errors; production build succeeds.
