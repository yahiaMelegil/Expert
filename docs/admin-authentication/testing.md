# Administrator Authentication Testing

Tests use PHPUnit through Laravel's test runner and an in-memory SQLite database.

## Run Administrator Authentication Tests

```bash
php artisan test tests/Feature/Admin/AuthenticationTest.php
```

## Run Regular-User Authentication Tests

```bash
php artisan test tests/Feature/Authentication/AuthenticationTest.php
```

## Run the Complete Project Suite

```bash
php artisan config:clear
php artisan test
```

## Check Formatting

```bash
vendor/bin/pint --test
```

## Covered Administrator Cases

- Successful active-administrator login
- Email normalization and restricted `admin:access` token creation
- Login validation failure
- Unknown email and incorrect password with the same generic response
- Inactive-administrator login rejection
- Valid administrator Bearer Token access to `me`
- Missing and invalid token rejection
- Regular-user token isolation
- Administrator token without the required ability
- Previously issued token blocked after administrator deactivation
- Current-token logout while preserving another device token
- Revoked-token rejection
- All-device logout without affecting another administrator
- Login rate limiting by normalized email and IP address
- Initial administrator creation through `AdminSeeder`
- Continued compatibility through the complete existing test suite
