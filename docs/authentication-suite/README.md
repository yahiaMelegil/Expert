# Integrated Authentication Suite

This project contains the regular-user, administrator, and expert authentication systems in one Laravel application. The three account types share Laravel Sanctum's polymorphic token storage while keeping their identities, lifecycle rules, routes, and authorization checks separate.

## Included Systems

| Account type | Model and table | Public entry points | Token ability | Account-state enforcement |
| --- | --- | --- | --- | --- |
| Regular user | `User` / `users` | Registration and login | `user:access` | Owner must be a `User` |
| Administrator | `Admin` / `admins` | Login only | `admin:access` | Owner must be an active `Admin` |
| Expert | `Expert` / `experts` | Registration and login | `expert:access` | Owner must be an active `Expert` |

Each protected area verifies both the Sanctum token and the token owner's model type. A token issued for one account type cannot be used in either of the other protected areas.

## Documentation Map

- [Architecture and isolation](architecture.md)
- [Authentication configuration](configuration.md)
- [Complete endpoint index](endpoints.md)
- [Setup and verification](setup-and-verification.md)
- [Integration verification report](verification-report.md)
- [Regular-user authentication](../authentication/README.md)
- [Administrator authentication](../admin-authentication/README.md)
- [Expert authentication](../expert-authentication/README.md)

## Project Layout

```text
app/
├── Enums/                         # Expert lifecycle values
├── Http/
│   ├── Controllers/Api/           # User, admin, and expert auth flows
│   ├── Middleware/                # Account-type and state isolation
│   ├── Requests/                  # Validation grouped by account type
│   └── Resources/                 # Safe API representations
├── Models/                        # User, Admin, and Expert identities
└── Notifications/Expert/          # Verification and reset emails
config/                            # Auth, admin seeding, and expert URLs
database/
├── factories/
├── migrations/
└── seeders/
docs/                              # Feature-specific documentation
routes/api.php                     # All authentication routes
tests/Feature/                     # Isolated and cross-account tests
```

The feature implementations remain grouped by account type. Shared framework files contain only the integration points: route registration, middleware aliases, exception envelopes, rate limiters, and authentication providers.
