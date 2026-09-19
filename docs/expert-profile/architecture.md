# Architecture

## Data model

| Table | Purpose | Ownership |
|---|---|---|
| `expert_profiles` | Public biography, slug, avatar metadata, publication state | One per expert |
| `expert_verified_scopes` | Admin-granted professional scope backed by an approved KYC application | Many per expert |
| `expert_availability_settings` | Service modes, weekly windows, blackout dates, capacity and response target | One per expert |

Every verified scope references the source KYC application. The administrator and timestamp remain auditable even if the public profile changes later. An old active scope for the same KYC domain is revoked when a replacement is granted.

## Publication rules

Publication requires all of the following:

1. Expert KYC status is `approved`.
2. At least one scope is `active` and not expired.
3. Professional title is present.
4. Biography contains at least 80 characters.
5. At least one public language is present.
6. At least one service mode is selected.
7. At least one weekly availability window is enabled.

The public endpoint re-evaluates account activity, KYC approval, and scope effectiveness on every request. A previously published profile therefore disappears immediately when the expert is disabled or all scopes expire or are revoked.

## Security boundaries

- Expert write endpoints require `auth:sanctum`, expert model validation, `expert:access`, and verified email.
- Scope fields have no expert write endpoint.
- Admin KYC approval validates that every granted scope matches the reviewed KYC domain and jurisdiction.
- The public resource excludes email, storage paths, blackout dates, token data, and review metadata.
- Profile writes, avatar uploads, publication, and public reads have dedicated rate limits.
