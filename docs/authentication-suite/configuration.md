# Authentication Configuration

Laravel 13 can supply package and framework defaults even when some configuration files are not published. This project keeps the authentication-critical configuration explicit so deployment behavior is visible, reviewable, and safely adjustable.

## Required Configuration Files

| File | Responsibility |
| --- | --- |
| `config/auth.php` | User provider plus the isolated expert provider and password broker |
| `config/sanctum.php` | Sanctum guards, token lifetime, token prefix, and middleware |
| `config/cors.php` | Browser origins, API paths, methods, headers, and credential policy |
| `config/hashing.php` | Password hashing driver and work factors |
| `config/admin.php` | Environment-backed initial administrator values |
| `config/expert.php` | Expert verification and password-reset frontend destinations |
| `config/mail.php` | Delivery transport for expert authentication notifications |
| `config/database.php` | Account, reset-token, and personal-access-token persistence |

The remaining standard Laravel files for the application, cache, filesystems, logging, queue, services, and sessions are also present. Framework configurations unrelated to this API are intentionally left at Laravel defaults until the corresponding feature is introduced.

## CORS

Browser API access is restricted by `CORS_ALLOWED_ORIGINS`. Supply every trusted frontend origin as a comma-separated value without URL paths:

```dotenv
CORS_ALLOWED_ORIGINS=https://app.example.com,https://admin.example.com
```

Local defaults cover ports `3000` and `5173` on both `localhost` and `127.0.0.1`. Production must replace them with the deployed frontend origins. Wildcard origins are not enabled.

The authentication APIs use Bearer Tokens, so `supports_credentials` is `false`. Do not enable cross-origin credentials unless the project deliberately adopts Sanctum's cookie-based SPA authentication and its CSRF flow.

## Sanctum

Personal access tokens are currently valid until revoked because `expiration` is `null`. Current-device logout, all-device logout, expert password reset, and expert password change apply the documented revocation policies.

`SANCTUM_TOKEN_PREFIX` is available for an optional production token prefix that can improve secret-scanning detection:

```dotenv
SANCTUM_TOKEN_PREFIX=
```

Changing the prefix affects only newly issued tokens.

## Password Hashing

The default hashing driver is bcrypt with `BCRYPT_ROUNDS=12`. Automated tests override the cost to keep the suite fast. Production should retain an appropriately benchmarked work factor.

After changing environment configuration in a deployed application, rebuild the configuration cache:

```bash
php artisan config:clear
php artisan config:cache
```
