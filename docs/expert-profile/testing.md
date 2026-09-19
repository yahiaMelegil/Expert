# Testing

Run the new feature tests:

```bash
php artisan test tests/Feature/Expert/Profile/ExpertProfileTest.php
php artisan test tests/Feature/Admin/Kyc/AdminKycTest.php
```

Run the complete project and formatter:

```bash
php artisan test
vendor/bin/pint --test
php artisan route:list --path=expert
```

The feature suite covers account-type isolation, account prefill, profile and availability writes, publication gating, public-data minimization, expiry behavior, avatar replacement/removal, and admin-created verified scopes.
