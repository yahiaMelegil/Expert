# Architecture and Security Boundaries

## Identity Model

The application uses three Eloquent authenticatable models:

- `User` represents customers using the platform.
- `Admin` represents internal platform administrators and has no public registration route.
- `Expert` represents experts and owns email-verification and future KYC state.

The email column is unique within each account table. The same normalized address may therefore belong to one account of each type. Expert password reset uses its own provider, broker, and token table so identical cross-account emails cannot cause broker collisions.

## Shared Sanctum Storage

All account types use `HasApiTokens`. Sanctum stores only token hashes in the shared `personal_access_tokens` table and links each record to its owner through `tokenable_type` and `tokenable_id`.

| Protected area | Middleware chain | Required owner | Required ability |
| --- | --- | --- | --- |
| Regular user | `auth:sanctum`, `regular-user`, `abilities:user:access` | `User` | `user:access` |
| Administrator | `auth:sanctum`, `admin`, `abilities:admin:access` | Active `Admin` | `admin:access` |
| Expert | `auth:sanctum`, `expert`, `abilities:expert:access` | Active `Expert` | `expert:access` |

Model-type checks and abilities intentionally work together. An ability is not accepted as proof that the owner belongs to the corresponding account type.

## Expert Lifecycle Separation

Expert authentication is independent from email verification and KYC approval:

- `is_active` controls whether the expert may log in or continue using an existing token.
- `email_verified_at` records control of the email address.
- `kyc_status` starts as `not_submitted` and is reserved for the future KYC module.

Registration and login do not approve KYC. Future verified or approved features must check the current database state dynamically rather than encode KYC approval into a long-lived token ability.

## Shared Integration Files

- `routes/api.php` defines the three route groups without changing their public URLs.
- `bootstrap/app.php` registers account middleware and consistent JSON exception responses.
- `app/Providers/AppServiceProvider.php` defines authentication rate limiters.
- `config/auth.php` adds only the expert provider and password broker required by the reset flow.

Controllers, requests, resources, models, notifications, factories, tests, and feature documentation remain separated by responsibility and account type.
