# KYC Backend Change Package

This archive contains only files added or modified for the backend KYC module. Copy its contents over the Laravel project root while preserving the directory structure.

## Added Files

### Domain and persistence

- `app/Enums/ExpertKycActorType.php` — audit actor types.
- `app/Enums/ExpertKycApplicationStatus.php` — detailed KYC states and state helpers.
- `app/Enums/ExpertKycDocumentType.php` — supported document slots.
- `app/Exceptions/InvalidKycTransitionException.php` — safe conflict exception for invalid transitions.
- `app/Models/ExpertKycApplication.php` — KYC attempt aggregate.
- `app/Models/ExpertKycExperience.php` — experience record.
- `app/Models/ExpertKycQualification.php` — qualification record.
- `app/Models/ExpertKycCredential.php` — certificate/license record.
- `app/Models/ExpertKycDocument.php` — private document metadata.
- `app/Models/ExpertKycStatusHistory.php` — transition audit record.
- `app/Services/Kyc/ExpertKycWorkflow.php` — draft, submission, retry, and review workflow.
- `app/Services/Kyc/ExpertKycDocumentStorage.php` — private storage, replacement, review, and orphan cleanup.
- `config/kyc.php` — KYC storage and upload limits.
- `database/factories/ExpertKycApplicationFactory.php` — test factory.

### HTTP API

- `app/Http/Controllers/Api/Expert/KycController.php` — expert KYC endpoints.
- `app/Http/Controllers/Api/Admin/KycController.php` — administrator review endpoints.
- `app/Http/Middleware/EnsureExpertEmailIsVerified.php` — verified-email requirement.
- `app/Http/Requests/Expert/Kyc/SaveKycApplicationRequest.php` — structured draft validation.
- `app/Http/Requests/Expert/Kyc/UploadKycDocumentRequest.php` — secure file validation.
- `app/Http/Requests/Admin/Kyc/ListKycApplicationsRequest.php` — review queue filters.
- `app/Http/Requests/Admin/Kyc/KycDecisionRequest.php` — decision reason validation.
- `app/Http/Requests/Admin/Kyc/ReviewKycDocumentRequest.php` — document review validation.
- `app/Http/Resources/Expert/Kyc/KycApplicationResource.php` — safe application response.
- `app/Http/Resources/Expert/Kyc/KycDocumentResource.php` — safe document metadata.
- `app/Http/Resources/Admin/Kyc/KycApplicationSummaryResource.php` — optimized list item.
- `app/Http/Resources/Admin/Kyc/KycApplicationDetailResource.php` — review detail and audit response.

### Migrations

- `database/migrations/2026_09_16_000000_add_registration_context_to_experts_table.php`
- `database/migrations/2026_09_16_000001_create_expert_kyc_applications_table.php`
- `database/migrations/2026_09_16_000002_create_expert_kyc_experiences_table.php`
- `database/migrations/2026_09_16_000003_create_expert_kyc_qualifications_table.php`
- `database/migrations/2026_09_16_000004_create_expert_kyc_credentials_table.php`
- `database/migrations/2026_09_16_000005_create_expert_kyc_documents_table.php`
- `database/migrations/2026_09_16_000006_create_expert_kyc_status_histories_table.php`

### Tests and documentation

- `tests/Feature/Expert/Kyc/ExpertKycTest.php` — expert flow, storage, ownership, retry, and rollback coverage.
- `tests/Feature/Admin/Kyc/AdminKycTest.php` — review queue, authorization, and decision coverage.
- `docs/kyc/README.md`
- `docs/kyc/architecture.md`
- `docs/kyc/endpoints.md`
- `docs/kyc/security.md`
- `docs/kyc/testing.md`
- `CHANGES.md` — this manifest.

## Modified Files

- `.env.example` — adds `KYC_FILESYSTEM_DISK`.
- `app/Http/Controllers/Api/Expert/Auth/AuthController.php` — persists expert registration context for KYC prefill.
- `app/Http/Requests/Expert/Auth/RegisterRequest.php` — validates optional registration context.
- `app/Http/Resources/Expert/ExpertResource.php` — returns country, language, and domain.
- `app/Models/Admin.php` — KYC review relationship.
- `app/Models/Expert.php` — safe fillable fields and KYC relationships.
- `app/Providers/AppServiceProvider.php` — KYC write, upload, submit, and decision rate limiters.
- `bootstrap/app.php` — KYC middleware alias and safe 404/409 JSON rendering.
- `config/filesystems.php` — private local `kyc` disk.
- `database/factories/ExpertFactory.php` — registration context defaults.
- `routes/api.php` — isolated expert/admin KYC routes.
- `tests/Feature/Expert/Auth/RegistrationTest.php` — verifies registration context persistence.

## Deployment Order

1. Back up the database and current application files.
2. Extract this archive into the Laravel project root.
3. Confirm the environment uses `KYC_FILESYSTEM_DISK=kyc` or omit it to use the same default.
4. Run the commands below.
5. Connect the frontend to the endpoints documented in `docs/kyc/endpoints.md`.

```bash
composer install
php artisan optimize:clear
php artisan migrate --force
php artisan test
```

No package was added and no `storage:link` command is required. The web-server/PHP process must have write access to `storage/app/private/kyc` (Laravel creates the nested directory when the first file is stored).

## Environment Variables

```dotenv
KYC_FILESYSTEM_DISK=kyc
```

Do not point this setting to the public disk. No credentials or secrets are included in the archive.

## Rollback

Back up KYC data and private files before rollback. If these seven migrations are the latest migrations and rollback is required immediately after deployment:

```bash
php artisan migrate:rollback --step=7 --force
php artisan optimize:clear
```

Then restore the replaced files from the pre-deployment backup and remove only unreferenced KYC files after confirming the database rollback. Rolling back the migrations deletes KYC application data; it does not automatically delete private files.

## Verification Performed

- Complete project test suite: 109 tests, 550 assertions, all passing.
- KYC suites: 19 tests, 141 assertions, all passing.
- `composer validate --strict --no-check-publish`: valid.
- Laravel KYC route inspection: 14 routes registered.
- Laravel Pint check on all changed PHP files: passing.
- `git diff --check`: passing.

The full-project `vendor/bin/pint --test` command still reports an existing import-order issue in the unchanged `app/Models/User.php`. That unrelated baseline file is intentionally not included in this package; every PHP file changed by this KYC task passes Pint.
