# Administrator Authentication Security

## Identity Isolation

- Administrators are stored in `admins`; customers remain in `users`.
- Both models use Sanctum's polymorphic token relation without sharing identities.
- No public administrator registration endpoint exists.
- The unique `admins.email` index supports efficient login lookup and prevents duplicate administrator emails.

## Route Protection

Protected administrator routes use this middleware order:

```text
auth:sanctum → admin → abilities:admin:access
```

- `auth:sanctum` validates and resolves the Bearer token.
- `admin` requires the token owner to be an active `Admin` model.
- `abilities:admin:access` requires the restricted administrator ability.

Model identity remains authoritative. Token abilities alone cannot turn a regular user into an administrator.

The `is_active` database value is checked on every protected request. Consequently, a previously issued token cannot be used while its administrator is inactive. When a future administration-management feature disables an account, it should also revoke that administrator's tokens as defense in depth.

## Credentials and Tokens

- Passwords are hashed through Laravel's `Hash` facade.
- Sanctum stores only a hash of each token in `personal_access_tokens`.
- A plain-text token is returned only in the login response that creates it.
- Passwords, remember tokens, and token hashes are excluded from API resources.
- Credentials and tokens must never be logged or included in URLs.
- Real administrator seed credentials belong in a secret manager or environment configuration, never source control.
- Production API traffic must use HTTPS.

## Login Protection

Administrator login is limited to five attempts per minute for each normalized-email and client-IP combination. The limiter runs before validation and database authentication, so blocked requests do not trigger an administrator query.

Unknown emails, incorrect passwords, and inactive administrators receive the same response. This avoids disclosing administrator account existence through response content.

## Token Revocation

- Current-device logout deletes only the token used by the request.
- All-device logout deletes tokens through the authenticated administrator's own token relationship.
- One administrator cannot revoke another administrator's tokens through these authentication endpoints.
- Revoked tokens fail `auth:sanctum` and return `401`.

## Future Authorization

The `admin:access` ability authorizes entry into the administration area; it is not a replacement for resource-level authorization. Future modules should add policies or permissions for actions such as managing administrators, resolving disputes, or viewing financial data.
