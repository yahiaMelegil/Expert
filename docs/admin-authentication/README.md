# Administrator Authentication

This feature provides an isolated Bearer Token authentication flow for platform administrators using Laravel Sanctum personal access tokens.

For general Sanctum installation and client token-handling guidance, see the existing [regular-user authentication documentation](../authentication/README.md).

## Selected Architecture

Administrators use a dedicated `Admin` model and `admins` table. This matches the current project because it has no roles or permissions system and keeps internal administration identities separate from customer identities.

Sanctum continues to use the existing polymorphic `personal_access_tokens` table. No additional authentication guard or provider is required for Bearer Token authentication because Sanctum resolves the token owner through its polymorphic relationship.

An administrator token must pass all three checks:

1. The token is valid according to `auth:sanctum`.
2. The token owner is an active `Admin` according to the `admin` middleware.
3. The token contains the `admin:access` ability.

A regular-user token cannot pass the second check, including regular-user tokens with Sanctum's wildcard ability.

## Database Setup

Run the new migration:

```bash
php artisan migrate
```

## Create the Initial Administrator

Set these values in the deployment environment or local `.env` file. Replace the placeholders with private values and use a password containing at least 12 characters.

```dotenv
ADMIN_NAME=<administrator-name>
ADMIN_EMAIL=<administrator-email>
ADMIN_PASSWORD=<strong-private-password>
```

Then run only the administrator seeder:

```bash
php artisan db:seed --class=AdminSeeder
```

The seeder normalizes the email, hashes the password, and creates an active administrator. It does not overwrite or reactivate an administrator if the configured email already exists.

Never commit real administrator credentials to source control. The supplied `.env.example` contains blank keys only.

## Authentication Flow

1. The administrator sends credentials to `POST /api/admin/auth/login`.
2. The request is rate-limited before the authentication query runs.
3. The API validates and normalizes the email.
4. The API verifies the password and confirms that the administrator is active.
5. Sanctum creates a token restricted to `admin:access`.
6. The plain-text token is returned once and sent as a Bearer token on protected requests.
7. Protected requests re-check the administrator model type and current `is_active` state.
8. Logout revokes either the current token or all tokens belonging to that administrator.

There is intentionally no public administrator registration endpoint.

See [endpoints.md](endpoints.md), [responses.md](responses.md), [security.md](security.md), and [testing.md](testing.md) for the complete contract.
