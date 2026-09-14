# Expert Authentication

This feature provides an isolated Bearer Token authentication system for experts using Laravel Sanctum personal access tokens. It includes registration, login, identity retrieval, email verification, password recovery and change, and current/all-device logout.

For shared Sanctum installation and client token-handling guidance, see [regular-user authentication](../authentication/README.md). The administrator flow remains documented under [administrator authentication](../admin-authentication/README.md).

## Selected Architecture

Experts use a dedicated `Expert` model and `experts` table. This matches the project's existing separate administrator architecture and prevents expert lifecycle fields from being mixed into regular customer accounts.

The same normalized email may exist once in each account type. Expert password resets therefore use a dedicated `experts` provider, `experts` broker, and `expert_password_reset_tokens` table. Sanctum continues to store hashed tokens in the shared polymorphic `personal_access_tokens` table.

## Authentication Is Not KYC Approval

Three states are deliberately separate:

1. `is_active` controls whether the account may authenticate and use existing tokens.
2. `email_verified_at` records email ownership.
3. `kyc_status` records future expert approval state.

Registration creates an active, unverified expert with `kyc_status=not_submitted`. The expert receives a token with only `expert:access` and may use the authentication/onboarding surface. Neither login nor email verification changes KYC status.

Future dashboard routes should require authenticated expert identity and verified email. Future approved-only routes must additionally check the current KYC state dynamically. Token abilities must never be treated as permanent proof of KYC approval.

## Setup

Install existing dependencies, configure the application, and run migrations:

```bash
composer install
php artisan migrate
```

Configure a public backend URL, application key, mail transport, and frontend URLs in the deployment environment:

```dotenv
APP_URL=https://api.example.com
EXPERT_FRONTEND_VERIFY_EMAIL_URL=https://app.example.com/expert/verify-email
EXPERT_FRONTEND_RESET_PASSWORD_URL=https://app.example.com/expert/reset-password
```

Generate an application key for a new environment with `php artisan key:generate`. Do not reuse the test key from `phpunit.xml` in any deployed environment.

See [architecture.md](architecture.md), [endpoints.md](endpoints.md), [email-verification.md](email-verification.md), [password-management.md](password-management.md), [security.md](security.md), and [testing.md](testing.md).
