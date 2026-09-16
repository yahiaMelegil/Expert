# Expert KYC Backend

This module provides the backend KYC workflow used by the expert and administrator dashboards. It is isolated from authentication: an expert may authenticate before approval, but KYC endpoints require an active expert account, a valid `expert:access` Sanctum token, and a verified email address.

## Installation

```bash
composer install
php artisan migrate
php artisan optimize:clear
```

No `storage:link` command is required. KYC documents use the private local disk at `storage/app/private/kyc` and are available only through authenticated download endpoints.

The optional environment setting is:

```dotenv
KYC_FILESYSTEM_DISK=kyc
```

The default value is already suitable for private local storage.

## Key Decisions

- `experts.kyc_status` remains the coarse authentication-facing status: `not_submitted`, `pending`, `approved`, or `rejected`.
- `expert_kyc_applications.status` is the detailed workflow status: `draft`, `submitted`, `under_review`, `needs_information`, `verified`, or `rejected`.
- Each resubmission after rejection or a request for information creates a new attempt. Earlier attempts remain immutable for audit purposes.
- Identity data is snapshotted when an application is created. The current account data is also returned separately to administrators.
- Files are stored privately with generated names. Internal paths and checksums are never serialized by API Resources.
- All active administrators currently have KYC review access because the project does not yet include a granular RBAC system.

See [architecture.md](architecture.md), [endpoints.md](endpoints.md), [security.md](security.md), and [testing.md](testing.md).
