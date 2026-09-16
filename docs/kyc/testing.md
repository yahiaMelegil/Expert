# KYC Testing

Run the KYC suites:

```bash
php artisan test tests/Feature/Expert/Kyc tests/Feature/Admin/Kyc
```

Run the complete project suite:

```bash
php artisan test
```

Check formatting and routes:

```bash
vendor/bin/pint --test
php artisan route:list --path=kyc
```

The tests use SQLite in memory and `Storage::fake('kyc')`. They cover prefill, email-verification enforcement, account-type isolation, draft persistence, validation, private upload/replacement/deletion, ownership, submission, retry attempts, administrator listing/filtering/pagination, document review, approval, rejection/change requests, invalid transitions, and compatibility with the existing authentication suite.
