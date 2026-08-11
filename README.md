# Sales & Stock ERP Mini

A compact Sales and Stock ERP assessment project built with CodeIgniter 3. The application is being developed in small, tested phases with server-side validation, secure warehouse authorization, and transactional stock integrity as core design goals.

## Current Status

Phases 1 through 8 are complete. The project includes the secure CodeIgniter foundation, database schema and seed data, a compiled Tailwind CSS ERP shell, catalog and warehouse inventory management, customer management, transactional sales invoicing, invoice history, session authentication, warehouse authorization, user administration, low-stock reporting with CSV export, and final clean-install/security verification.

## Features

- Structured ERP shell with desktop sidebar, responsive mobile navigation, top bar, breadcrumbs, feedback messages, and reusable page patterns
- Product listing with name/code search, category filtering, preserved-filter pagination, and empty states
- Product create/edit forms with server-side validation and duplicate-code protection
- POST-only product enable/disable actions with CSRF protection and confirmation prompts
- Category listing, create/edit forms, unique-name validation, product counts, and protected deletion
- Warehouse inventory listing with product search, warehouse filtering, pagination, health indicators, and quantity adjustment
- Automatic zero-quantity inventory initialization for every warehouse when a product is created
- Warehouse listing, create/edit forms, unique codes, transactional product initialization, and soft enable/disable controls
- Searchable customer directory with pagination, create/edit forms, and invoice-safe deletion
- Sales invoice builder with customer/warehouse selection, vanilla-JavaScript AJAX product search, dynamic lines, percentage discount, and immediate displayed totals
- Trusted server-side prices and calculations, deterministic inventory row locking, insufficient-stock rejection, and atomic stock deduction
- Filterable invoice history and read-only invoice detail pages with server-enforced warehouse scope
- Session login/logout using bcrypt password verification and session-ID regeneration
- `admin` organization-wide access and server-enforced `user_warehouse` access to the assigned warehouse only
- Administrator-only user management with search, role filters, create/edit, secure password replacement, warehouse assignment, and protected deletion
- Warehouse-scoped low-stock report with product search, warehouse filter, shortage calculation, summaries, and pagination
- Filter-preserving low-stock CSV export limited to the signed-in user's authorized warehouse scope
- Output escaping for database and submitted values
- Repeat-safe database schema and demonstration data

## Requirements

- PHP 7.4 or newer with the `mysqli` extension
- MySQL 8.0+ or a compatible MariaDB version
- Apache with `mod_rewrite` enabled, or another web server configured to route requests through `index.php`
- A writable `application/cache/sessions` directory
- CodeIgniter 3.1.13 (included in this repository)

Node.js/npm is required only when rebuilding Tailwind CSS. The compiled stylesheet is checked in, so normal application installation requires only PHP, MySQL/MariaDB, and the web server.

## Local Installation

1. Place or clone the project at `C:\laragon\www\erp_stocks_sales`.
2. Start Apache and MySQL in Laragon.
3. Import `database/database.sql` through phpMyAdmin, HeidiSQL, or the MySQL client.
4. Ensure the Laragon virtual host points to `http://erp_stocks_sales.test/`.
5. Open `http://erp_stocks_sales.test/` in a browser.

Example PowerShell database import:

```powershell
Get-Content .\database\database.sql | mysql -u root -p
```

If the local root account has no password, omit `-p`.

## Configuration

The checked-in defaults in `application/config/database.php` match a typical Laragon installation:

- Host: `localhost`
- Username: `root`
- Password: empty
- Database: `erp_stocks_sales`

You can override these without committing secrets by setting:

- `ERP_DB_HOST`
- `ERP_DB_USERNAME`
- `ERP_DB_PASSWORD`
- `ERP_DB_DATABASE`

The default base URL is configured in `application/config/config.php` as `http://erp_stocks_sales.test/`. Set `ERP_BASE_URL` to override it; include the trailing slash.

CSRF protection is enabled. Token regeneration is deliberately disabled so normal forms and later concurrent AJAX calls can share the session token safely. The token still expires with its cookie/session lifecycle.

For production, set `CI_ENV=production`. The front controller accepts this through either the web-server variable or process environment. For HTTPS, set `ERP_BASE_URL` to the HTTPS URL and `ERP_COOKIE_SECURE=1`. Do not expose database errors or commit production credentials.

Apache uses the checked-in `.htaccess` to deny direct HTTP access to application/framework code, repository metadata, dependencies, tests, local instruction files, and `database/database.sql`. On Nginx, Caddy, IIS, Herd, or another server that does not honor `.htaccess`, configure equivalent deny rules or use a document root that exposes only the front controller and public assets.

## Demo Credentials

Passwords in the SQL seed are bcrypt hashes generated by `password_hash()` and verified by the login flow with `password_verify()`.

| Role | Email | Password | Warehouse |
| --- | --- | --- | --- |
| Administrator | `admin@example.com` | `Admin@123` | All warehouses |
| Warehouse user | `warehouse@example.com` | `Warehouse@123` | Main Warehouse |

These credentials are for local demonstration only and must be replaced outside the assessment environment.

## Seed Data

The repeat-safe seed includes:

- 3 categories
- 3 warehouses
- 6 products, including one inactive product
- Different quantities for every product and warehouse
- 3 customers
- 1 administrator and 1 warehouse-assigned user

## Current Routes

- `/` — ERP dashboard
- `/home` — ERP dashboard
- `/login` — public sign-in page
- `/logout` — POST-only session logout
- `/users` — administrator-only user directory, search, and role filter
- `/users/create` — create an administrator or warehouse user
- `/users/edit/{id}` — edit account access or replace its password
- `/products` — product list, search, category filter, and pagination
- `/products/create` — add product
- `/products/edit/{id}` — edit product
- `/categories` — category list and product counts
- `/categories/create` — add category
- `/categories/edit/{id}` — edit category
- `/stock` — warehouse inventory, search, filter, and pagination
- `/stock/edit/{warehouse_id}/{product_id}` — adjust one warehouse/product quantity
- `/warehouses` — warehouse management and operational status
- `/warehouses/create` — add a warehouse
- `/warehouses/edit/{id}` — edit a warehouse
- `/customers` — customer directory and search
- `/customers/create` — add a customer
- `/customers/edit/{id}` — edit a customer
- `/sales` — authorized invoice history, search, warehouse filter, summaries, and pagination
- `/sales/create` — build and save a sales invoice
- `/sales/view/{id}` — read-only authorized invoice details
- `/sales/search-products` — warehouse-scoped product search JSON endpoint
- `/reports/low-stock` — authorized low-stock report, warehouse filter, product search, and shortage summary
- `/reports/low-stock/csv` — CSV export of the currently filtered, authorized low-stock data

Product, category, inventory, warehouse, customer, user, sales, and logout writes use explicit POST routes. Categories assigned to products, customers assigned to invoices, and users with attributed invoices cannot be deleted. Warehouse and user management are administrator-only; warehouse users are restricted server-side on inventory, sales, dashboard summaries, and reports.

## Tailwind CSS Development

Tailwind CSS and its CLI are pinned at `4.3.3` as development dependencies. Install the build tooling and compile the checked-in stylesheet with:

```powershell
npm install
npm run css:build
```

For active UI development:

```powershell
npm run css:watch
```

The source file is `assets/css/input.css`; the generated runtime asset is `assets/css/app.css`. PHP views and application JavaScript are explicitly registered as Tailwind sources. Do not edit the generated stylesheet by hand.

## Interface Design Direction

The application uses Tailwind CSS and a structured ERP interface rather than independent page layouts. The shared shell includes:

- A desktop sidebar and responsive mobile navigation
- A top bar with user and warehouse context
- Navigation groups for Dashboard, Catalog, Inventory, Sales, and Reports, with signed-in account context and logout
- Consistent page headers, breadcrumbs, action areas, filter toolbars, tables, forms, badges, alerts, and empty states
- Compact, readable information density suitable for routine business operations
- Accessible focus states, form labels, semantic markup, and responsive behavior

Tailwind is compiled locally into the checked-in application stylesheet. The runtime does not depend on the Tailwind Play CDN.

## Final Verification

Phase 8 was verified on August 12, 2026 using PHP 7.4.33 and an isolated database created from the checked-in SQL and removed after testing.

- Imported `database/database.sql` twice successfully to confirm clean installation and repeat safety
- Confirmed 8 tables, 3 categories, 3 warehouses, 6 products, 18 unique inventory positions, 3 customers, and 2 hashed demo users
- Signed in with both demonstration roles against the clean database
- Created an invoice while submitting tampered browser price/total values; the server saved trusted totals and deducted only the selected warehouse stock
- Confirmed insufficient stock rolls back the complete invoice
- Confirmed GET requests cannot perform mutations and POST requests without a CSRF token are rejected
- Confirmed warehouse users cannot access another warehouse through page, AJAX, invoice, or CSV parameters
- Ran PHP syntax checks, JavaScript syntax checks, the production Tailwind build, Git consistency checks, and live HTTP smoke tests

## Important Assumptions

- Money is stored in `DECIMAL` columns and will be rounded to two decimal places server-side.
- Product disabling is soft through `products.is_active`.
- Inventory is represented by one unique `warehouse_products` row per warehouse and product.
- Newly created products receive a zero-quantity inventory row for every current warehouse within the product creation transaction.
- Newly created warehouses receive a zero-quantity inventory row for every current product within the warehouse creation transaction.
- Warehouses are soft-disabled rather than deleted; locations assigned to users cannot be disabled until those users are reassigned.
- Customers can be deleted only while they have no invoice history; referenced customers are preserved.
- Invoice lines are normalized by product ID, while prices, stock, discounts, and totals are recalculated from database values inside the save transaction.
- Inventory rows are locked in ascending product ID order with `SELECT ... FOR UPDATE`; any unavailable line rolls back the complete invoice.
- Invoice numbers are finalized from the inserted sale ID, backed by the database unique constraint.
- Saved invoices record the signed-in user ID. Warehouse users may search, adjust stock, report on, and sell only from their assigned warehouse; administrators may access all warehouses.
- Invoice history and detail queries apply the same server-side warehouse scope; an out-of-scope invoice ID is returned as not found.
- User passwords are accepted only when 8–72 characters and are stored with `password_hash()`. The final administrator cannot be demoted or deleted, and users cannot delete their own signed-in account.
- A low-stock position is defined as `quantity <= alert_quantity`; shortage is `max(alert_quantity - quantity, 0)`.
- Low-stock CSV exports preserve active product/warehouse filters, neutralize spreadsheet formula prefixes in text cells, and never export outside the current user's authorized scope.
- Successful inventory quantities cannot be negative; the database includes supporting check constraints, while invoice business rules will enforce this inside transactions.
- The initial SQL uses `CREATE TABLE IF NOT EXISTS` and `INSERT IGNORE`, so re-importing does not delete existing data.
- UI components should remain reusable CI3 view partials and use Tailwind utilities consistently instead of accumulating page-specific CSS.
