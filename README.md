# Expert Platform Backend

A unified Laravel 13 authentication backend for regular users, platform administrators, and experts. It uses Laravel Sanctum personal access tokens, Bearer authentication, account-specific abilities, and explicit model-type middleware to keep all three security areas isolated.

## Authentication Systems

| Account type | Identity model | Token ability | Available flows |
| --- | --- | --- | --- |
| Regular user | `App\Models\User` | `user:access` | Register, login, profile, current-device logout, all-device logout |
| Administrator | `App\Models\Admin` | `admin:access` | Login, profile, current-device logout, all-device logout |
| Expert | `App\Models\Expert` | `expert:access` | Register, login, profile, email verification, password recovery/change, logout |

Administrator registration is intentionally unavailable over the public API. Experts begin with `kyc_status=not_submitted`; authentication and email verification never imply KYC approval.

## Requirements

- PHP 8.3+
- Composer
- SQLite, MySQL, PostgreSQL, or another Laravel-supported database
- A mail transport for expert verification and password-reset messages
- HTTPS in production

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
php artisan serve
```

The example environment uses SQLite. Configure the `DB_*`, `MAIL_*`, `APP_URL`, `CORS_ALLOWED_ORIGINS`, and expert frontend URL variables for the target environment before deployment.

To create the initial administrator, configure the blank `ADMIN_*` values privately and run:

```bash
php artisan db:seed --class=AdminSeeder
```

## API Documentation

- [Integrated architecture, endpoint index, and setup](docs/authentication-suite/README.md)
- [Sanctum, CORS, and hashing configuration](docs/authentication-suite/configuration.md)
- [Regular-user authentication](docs/authentication/README.md)
- [Administrator authentication](docs/admin-authentication/README.md)
- [Expert authentication](docs/expert-authentication/README.md)

All protected requests use:

```http
Accept: application/json
Authorization: Bearer {plain_text_token}
```

The plain-text token is returned only when created. Sanctum stores its hash in the database.

## Verification

```bash
php artisan route:list --path=api --except-vendor
vendor/bin/pint --test
php artisan test
```

The automated suite covers success and failure paths, rate limiting, email verification, password management, token revocation, active-account checks, and cross-account token isolation.

## Security

Never commit `.env`, real credentials, plain-text tokens, runtime databases, logs, caches, or generated artifacts. Use unique production credentials, a configured mail provider, a valid application key, and HTTPS for every authentication request.
