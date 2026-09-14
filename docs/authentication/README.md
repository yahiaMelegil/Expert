# Regular User Authentication

This feature provides token-based authentication for regular platform users with Laravel Sanctum personal access tokens. A successful registration or login creates a token that the client sends as a Bearer token on protected requests.

## Requirements

- PHP 8.3 or newer
- Composer
- A database supported by Laravel
- HTTPS in production

## Installation

From the project root:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database connection in `.env`, then run:

```bash
php artisan migrate
php artisan serve
```

The Sanctum package and its token migration are already included in this project. Do not run `php artisan install:api` again for this implementation.

## Authentication Flow

1. The client calls `POST /api/register` or `POST /api/login`.
2. The API validates the request and, when applicable, verifies the password.
3. Sanctum creates a hashed personal access token restricted to `user:access`.
4. The API returns the plain-text token once, in that response only.
5. The client sends the token in the `Authorization` header on protected requests.
6. `POST /api/logout` revokes only the current token.
7. `POST /api/logout-all` revokes all tokens owned by the authenticated user.

## Required Headers

All API requests should include:

```http
Accept: application/json
Content-Type: application/json
```

Protected requests must also include:

```http
Authorization: Bearer {token}
```

## Frontend Token Handling

- Treat the token like a password. Never log it, expose it in a URL, or send it to third parties.
- For browser clients, keeping the token only in application memory minimizes persistence risk. Browser storage such as `localStorage` remains readable by JavaScript and can be stolen by an XSS vulnerability.
- If persistent browser login is a product requirement, document the accepted risk and use strong XSS protections such as a strict Content Security Policy and disciplined output escaping. A future first-party SPA may instead adopt Sanctum's HttpOnly cookie flow.
- Native mobile and desktop clients should use the operating system's protected credential storage.
- Remove the local token immediately after a successful logout response. For `logout-all`, remove it on every client where possible.

## Security Notes

- Passwords are hashed with Laravel's `Hash` facade.
- Passwords and remember tokens are hidden and are not included in API resources.
- Protected routes verify the token owner is a `User` and require the `user:access` ability; administrator and expert tokens are rejected.
- Registration and login are limited to five attempts per minute per client IP.
- Login failures use the same message whether the email is unknown or the password is incorrect.
- Tokens use Sanctum's default lifetime and remain valid until revoked unless an expiration policy is configured later.
- Production traffic must use HTTPS.

See [endpoints.md](endpoints.md) for the API contract, [responses.md](responses.md) for response envelopes, and [testing.md](testing.md) for test instructions.
