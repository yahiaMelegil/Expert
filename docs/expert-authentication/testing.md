# Testing

Run the expert authentication suite:

```bash
php artisan test tests/Feature/Expert
```

Run all project tests to verify regular-user and administrator compatibility:

```bash
php artisan test
```

Run formatting and inspect routes:

```bash
vendor/bin/pint --test
php artisan route:list --path=api/expert -v
```

The tests cover registration validation and rollback, cross-account email behavior, login states, token ability and model isolation, email notification and signed-link validation, idempotent verification, rate limits, password broker isolation, valid/invalid/expired reset tokens, password token-revocation policies, inactive accounts, and scoped logout.

`phpunit.xml` contains a non-secret all-zero key only for deterministic test encryption and signed URLs. Every deployed environment must supply its own generated `APP_KEY`.
