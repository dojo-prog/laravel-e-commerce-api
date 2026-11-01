# E-commerce API

A REST API for an e-commerce application built with Laravel. It provides customer authentication, product and category browsing, carts, addresses, coupons, checkout, orders, and a mock payment provider.

## Contents

- [Requirements](#requirements)
- [Local development setup](#local-development-setup)
- [Environment configuration](#environment-configuration)
- [Database migrations and seed data](#database-migrations-and-seed-data)
- [Authentication](#authentication)
- [API overview](#api-overview)
- [Checkout and payment flow](#checkout-and-payment-flow)
- [Product and category image storage](#product-and-category-image-storage)
- [Testing and code quality](#testing-and-code-quality)
- [Project structure](#project-structure)
- [Operational and security notes](#operational-and-security-notes)

## Requirements

- PHP 8.3 or later
- Composer
- PostgreSQL
- Node.js and npm if you are changing or building frontend assets

The application targets PostgreSQL. Some schema details and database operations are PostgreSQL-specific, so use PostgreSQL for local development and deployment.

## Local development setup

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Create the local environment file:

   ```bash
   cp .env.example .env
   ```

   On PowerShell, use:

   ```powershell
   Copy-Item .env.example .env
   ```

3. Create a PostgreSQL database and update the database values in `.env`:

   ```dotenv
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=laravel
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. Generate the application key, migrate the schema, and seed local demo data:

   ```bash
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   ```

5. Start the development server:

   ```bash
   php artisan serve
   ```

The default base URL is `http://localhost:8000`; the versioned API base path is `http://localhost:8000/api/v1`.

To run Laravel's integrated development processes, see the `dev` script in `composer.json`. Frontend assets can be installed and built with:

```bash
npm install
npm run build
```

## Environment configuration

The `.env.example` file is the starting point for local configuration. In addition to the database settings above, review these values for your environment:

| Variable | Purpose |
| --- | --- |
| `APP_ENV` | Environment name. The main demo seeder only runs in `local` and `testing`. |
| `APP_DEBUG` | Enables detailed exception output. Keep disabled in production. |
| `APP_URL` | Application URL used when generating links. |
| `BCRYPT_ROUNDS` | Password hashing work factor. |
| `MOCK_PAYMENT_OUTCOME` | Mock gateway result: `paid` (default) or `failed`. |
| `CLOUDINARY_CLOUD_NAME` | Cloudinary cloud name for uploaded category/product images. |
| `CLOUDINARY_API_KEY` | Cloudinary API key. |
| `CLOUDINARY_API_SECRET` | Cloudinary API secret. |
| `CLOUDINARY_FOLDER` | Cloudinary folder for application uploads. |

Keep actual credentials and secrets in `.env` or a managed secret store. Do not commit populated environment files.

## Database migrations and seed data

Run migrations with:

```bash
php artisan migrate
```

The default seeder is intended for local and test environments only:

```bash
php artisan db:seed
```

It is safe to run repeatedly and creates or updates the following demo data:

- The local administrator account `admin@dojo.com` with password `testPass`
- The `Apparel` and `Home and Living` categories
- The `Everyday Tee`, `Canvas Tote`, and `Ceramic Mug` products with placeholder images
- The `WELCOME10` and `SAVE5` coupons
- A demo customer (`demo.customer@dojo.com`) with an address, cart, and cart item
- A confirmed sample order with order items, a paid mock payment, and status history

The demo password is defined by the local admin seeder. It is not a production credential; change or disable it before exposing a seeded account outside a disposable development environment. Demo data is not intended for production.

To rebuild a **disposable** database from scratch:

```bash
php artisan migrate:fresh --seed
```

> **Warning:** `migrate:fresh` drops every table in the configured database before migrating and seeding. Confirm the selected connection and database name first; never run it on a database containing data you need.

The schema uses UUID identifiers. Foreign-key dependencies mean catalog records must exist before related cart and order records; the main seeder calls its component seeders in that order.

## Authentication

The API uses Laravel Sanctum bearer tokens for protected API requests. Register a customer, then log in to receive a token:

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Example Customer",
    "email": "customer@example.com",
    "password": "example-password",
    "password_confirmation": "example-password"
  }'
```

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "customer@example.com",
    "password": "example-password"
  }'
```

The login response includes a `data.token` value. Send it as a bearer token on authenticated requests:

```bash
curl http://localhost:8000/api/v1/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Use `POST /api/v1/logout` with the same bearer token to revoke the current access token. Login attempts are rate-limited. Protected resources are scoped to the current customer where applicable; administrative operations require the administrator role.

> **Security warning:** The current registration request accepts an optional `role` value, including `admin`. Do not expose this registration endpoint in a production deployment until registration is changed to always assign the customer role and ignore client-supplied roles. The administrator-only endpoint descriptions below assume the caller has an authorized admin account; the current registration behavior does not safely enforce how that role is granted.

## API overview

All endpoints are under `/api/v1`. Requests with a JSON body should send `Accept: application/json` and `Content-Type: application/json`. Use `multipart/form-data` for image uploads.

### Public catalog endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/categories` | List categories; supports pagination and search. |
| `GET` | `/categories/{category}` | Get a category. |
| `GET` | `/products` | List products; supports pagination and filters. |
| `GET` | `/products/{product}` | Get a product. |
| `GET` | `/products/{product}/images` | List images for a product. |

### Authentication endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/register` | Register an account. |
| `POST` | `/login` | Authenticate and issue a Sanctum token. |
| `POST` | `/logout` | Revoke the current token; requires authentication. |
| `POST` | `/forgot-password` | Request a password reset link. |
| `POST` | `/reset-password` | Reset a password using a reset token. |
| `GET` | `/verify-email/{id}/{hash}` | Verify an email using a signed link; requires authentication. |
| `POST` | `/email/verification-notification` | Send a verification notification; requires authentication. |

### Authenticated customer endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/user` | Get the authenticated user. |
| `GET`, `POST` | `/addresses` | List or add the current user's addresses. |
| `GET`, `PUT`, `PATCH`, `DELETE` | `/addresses/{address}` | Read, update, or delete an address. |
| `GET` | `/cart` | Get the current user's cart. |
| `POST` | `/cart-items` | Add an item to the current user's cart. |
| `GET`, `PUT`, `PATCH`, `DELETE` | `/cart-items/{cart_item}` | Read, update, or remove a cart item. |
| `POST` | `/checkout` | Place an order from the current cart and process its initial payment. |
| `GET` | `/orders` | List orders visible to the authenticated user. |
| `GET` | `/orders/{order}` | Get an order visible to the authenticated user. |
| `POST` | `/orders/{order}/payments` | Retry payment for an eligible pending order. |
| `GET` | `/order-status-histories/{history}` | View an authorized order status history entry. |

### Administrator operations

The following authenticated routes are intended for administrators and are additionally authorized by policies or form requests:

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST`, `PUT`, `PATCH`, `DELETE` | `/categories[/{category}]` | Create, update, or delete categories. |
| `POST`, `PUT`, `PATCH`, `DELETE` | `/products[/{product}]` | Create, update, or delete products. |
| `POST` | `/product-images` | Upload an image for a product. |
| `DELETE` | `/product-images/{image}` | Delete a product image. |
| `GET`, `POST`, `PUT`, `PATCH`, `DELETE` | `/coupons[/{coupon}]` | Manage coupons. |
| `GET` | `/order-status-histories` | List order status histories. |

For the authoritative list of registered methods and middleware, run:

```bash
php artisan route:list --path=api/v1
```

### List filters

List endpoints return paginated resource collections. Current supported query parameters include:

| Endpoint | Parameters |
| --- | --- |
| `GET /categories` | `page`, `per_page`, `search`, `sort_by` (`created_at`, `name`), `sort_order` (`asc`, `desc`). |
| `GET /products` | `page`, `per_page`, `search`, `category_id`, `min_price`, `max_price`, `status`, `in_stock`, `sort_by` (`created_at`, `name`), `sort_order` (`asc`, `desc`). |
| `GET /orders` | `page`, `per_page` (maximum 100), `status`, `payment_status`, `from`, `to`, `order_number`, `sort_by` (`created_at`), `sort_order` (`asc`, `desc`). |

Order listing and individual order access are customer-scoped. Administrators can query all orders, subject to the relevant authorization policy.

### Example: add an item to the cart

Replace `PRODUCT_UUID` with an ID returned from `GET /api/v1/products`:

```bash
curl -X POST http://localhost:8000/api/v1/cart-items \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "product_id": "PRODUCT_UUID",
    "quantity": 2
  }'
```

### Example: add a shipping address

```bash
curl -X POST http://localhost:8000/api/v1/addresses \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "recipient_name": "Example Customer",
    "phone": "09171234567",
    "address_line_1": "123 Example Street",
    "city": "Cebu City",
    "province": "Cebu",
    "postal_code": "6000",
    "country": "Philippines",
    "is_default": true
  }'
```

Use the returned address UUID in the checkout request. The address must belong to the authenticated user.

## Checkout and payment flow

Checkout uses the authenticated customer's existing cart and stored shipping address. The API calculates prices and totals server-side, reserves stock, snapshots order item and address details, clears the cart, and creates the initial payment attempt. The mock provider then processes that attempt.

Send a unique `Idempotency-Key` header for each checkout operation. Repeating the same request with the same key avoids creating a duplicate order; using the same key for a different request is rejected.

```bash
curl -X POST http://localhost:8000/api/v1/checkout \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Idempotency-Key: checkout-example-001" \
  -d '{
    "address_id": "ADDRESS_UUID",
    "payment_method": "mock",
    "coupon_code": "WELCOME10",
    "notes": "Please leave the package at the front desk."
  }'
```

`address_id`, `payment_method`, and `Idempotency-Key` are required. The only supported payment method is currently `mock`; coupon code and notes are optional. A successful mock payment confirms the order. Set `MOCK_PAYMENT_OUTCOME=failed` to simulate a failed attempt; the default outcome is `paid`. Failed payments can be retried through `POST /api/v1/orders/{order}/payments` while the order remains pending. A retry creates a new attempt after a failed attempt.

Order responses include order items, payment attempts, and status history when those relationships are loaded. The payment integration is a development mock and must not be treated as a real payment processor.

## Product and category image storage

Product and category image uploads use Cloudinary. Configure the Cloudinary environment variables before using the upload endpoints. Catalog seed images are placeholders and do not require uploads. Image files sent to the API must satisfy its image type and size validation.

## Testing and code quality

Run the application test suite with:

```bash
php artisan test
```

The test configuration in `phpunit.xml` uses an in-memory SQLite connection. The production-style schema and local application database use PostgreSQL; validate database-specific behavior against PostgreSQL as part of local integration checks.

Format modified PHP files with Laravel Pint:

```bash
vendor/bin/pint --dirty --format agent
```

## Project structure

| Path | Purpose |
| --- | --- |
| `app/Actions/` | Checkout and payment business operations. |
| `app/Contracts/` | Contracts for integrations such as payment gateways. |
| `app/Http/Controllers/Api/V1/` | Versioned API controllers. |
| `app/Http/Requests/` | Request validation and authorization. |
| `app/Http/Resources/` | API response serialization. |
| `app/Models/` | Eloquent models and relationships. |
| `app/Policies/` | Resource authorization rules. |
| `database/migrations/` | Database schema history. |
| `database/seeders/` | Development and test seed data. |
| `routes/api.php` | Versioned application API routes. |
| `routes/auth.php` | Authentication routes. |

## Operational and security notes

- Do not use demo accounts, seeded personal data, or the mock payment provider in production.
- Do not run `migrate:fresh` against a database containing data you need.
- Keep `APP_DEBUG=false` in production and protect database and Cloudinary credentials.
- Admin access must be granted only through a trusted administrative process. Do not expose administrator credentials in client applications or public repositories.
- Review endpoint authorization and rate limits when adding routes or changing permissions.
