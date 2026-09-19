# Expert Verified Scope and Public Profile

This module turns an approved KYC decision into a controlled, public expert identity. User and expert accounts remain separate authenticatable models and a user or administrator token cannot call expert profile routes.

## Responsibilities

- **Administrator:** approves KYC and defines the verified scope: domain, jurisdiction, professional role, service types, languages, and optional expiry.
- **Expert:** maintains the public biography, avatar, specialties, availability, capacity, and publication state.
- **Platform:** publishes only when the expert is active, email verified, KYC approved, has an effective verified scope, and meets the minimum profile and availability requirements.

KYC approval and public publication are intentionally different decisions. Approval creates the trusted scope; the expert still chooses when a complete profile becomes public.

## Setup

```bash
php artisan migrate
php artisan storage:link
php artisan optimize:clear
```

Avatars are stored locally on Laravel's `public` disk. The symbolic link is required to serve them. KYC documents remain on their existing private disk and are not exposed by this module.

See [architecture.md](architecture.md), [endpoints.md](endpoints.md), and [testing.md](testing.md).
