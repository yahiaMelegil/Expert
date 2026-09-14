# Architecture

## Account Model

`Expert` is a separate authenticatable model with its own `experts` table. It uses `HasApiTokens`, `HasFactory`, and `Notifiable`, and implements Laravel's `MustVerifyEmail` contract.

The table contains only authentication and account-state fields required now:

| Field | Purpose |
| --- | --- |
| `name` | Initial expert display name |
| `email` | Normalized, unique expert identifier |
| `email_verified_at` | Email-verification timestamp |
| `password` | Secure password hash |
| `is_active` | Dynamic suspension/availability gate |
| `kyc_status` | Future approval state, initially `not_submitted` |
| `remember_token` | Laravel password lifecycle compatibility |

Expert profiles, specialties, qualifications, documents, pricing, availability, and KYC application records are intentionally outside this feature.

## Authentication Boundary

Every protected expert request passes three checks in this order:

1. `auth:sanctum` validates the personal access token.
2. `expert` verifies that the token owner is an active `Expert` model.
3. `abilities:expert:access` verifies the restricted token ability.

This prevents regular-user and administrator tokens from entering the expert area, including tokens with Sanctum's wildcard ability. Account activity is checked on every request, so disabling an expert immediately blocks previously issued tokens.

## Email and Password Isolation

An email address may belong to a regular user, administrator, and expert because each account type has its own table. Within `experts`, the email is normalized by the request layer and model mutator, then protected by a unique database constraint.

Expert reset tokens are stored in `expert_password_reset_tokens`. This prevents an expert reset request from replacing a regular user's reset token when both accounts share an email address.

## KYC Extension Point

`ExpertKycStatus` defines `not_submitted`, `pending`, `approved`, and `rejected`. Authentication reads and returns the state but never changes it. A future KYC module should own all transitions and add dedicated authorization middleware for verified and approved-only routes.
