# Setup and Verification

## Requirements

- PHP 8.3 or newer
- Composer
- A Laravel-supported database
- A configured mail transport for expert verification and password reset
- HTTPS for every production API request

## Local Setup

From the project root:

```bash
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

The default example configuration uses SQLite. Configure the relevant `DB_*` values instead when using another database.

Set expert frontend destinations and production mail settings in `.env`:

```dotenv
APP_URL=https://api.example.com
CORS_ALLOWED_ORIGINS=https://app.example.com,https://admin.example.com
EXPERT_FRONTEND_VERIFY_EMAIL_URL=https://app.example.com/expert/verify-email
EXPERT_FRONTEND_RESET_PASSWORD_URL=https://app.example.com/expert/reset-password
```

## Initial Administrator

Set private deployment values for `ADMIN_NAME`, `ADMIN_EMAIL`, and a password containing at least 12 characters. Then run:

```bash
php artisan db:seed --class=AdminSeeder
```

Do not commit the resulting credentials or a populated `.env` file.

## Verification Commands

```bash
php artisan migrate:status
php artisan route:list --path=api --except-vendor
vendor/bin/pint --test
php artisan test
```

The test suite uses an in-memory SQLite database and covers each authentication flow, token revocation, account-state enforcement, and rejection of tokens across account types.

## Source-Package Hygiene

The distributable project intentionally excludes dependencies and runtime artifacts such as `vendor`, `node_modules`, `.env`, SQLite runtime databases, caches, compiled views, logs, and test-result caches. Recreate dependencies and runtime state with the setup commands above.
