# Authentication Testing

The authentication feature tests use PHPUnit through Laravel's test runner and an in-memory SQLite database. They do not modify the development database.

## Run Authentication Tests Only

```bash
php artisan test tests/Feature/Authentication/AuthenticationTest.php
```

## Run All Feature Tests

```bash
php artisan test --testsuite=Feature
```

## Run the Complete Test Suite

```bash
php artisan config:clear
php artisan test
```

## Check Code Style

```bash
vendor/bin/pint --test
```

## Covered Cases

- Successful registration and token creation
- Registration validation failure
- Duplicate email rejection, including normalized email casing
- Password hashing and safe user serialization
- Successful login and token creation
- Generic invalid-credential response
- Protected endpoint access with a valid Bearer token
- Protected endpoint rejection without a token
- Rejection of administrator and expert tokens on regular-user routes
- Rejection of a regular-user token without the `user:access` ability
- Current-token logout while preserving other device tokens
- Rejection of a revoked token
- All-device logout and complete token revocation
- Login rate limiting and the `429` response envelope
