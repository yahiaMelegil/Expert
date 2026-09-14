# Security

- Expert passwords are hashed with Laravel's `Hash` facade and never serialized.
- Credential failures use one generic message for unknown email, wrong password, and inactive accounts.
- Email addresses are normalized before queries and writes, and `experts.email` is unique.
- Sanctum stores token hashes; plain-text tokens are returned only at registration or login.
- Protected routes validate token authenticity, model type, live account state, and `expert:access` ability.
- Regular-user and administrator tokens receive `403` on expert routes.
- Disabling an expert blocks old tokens immediately because `is_active` is checked per request.
- Verification links are signed, expire after 60 minutes, and bind the expert ID to an email hash.
- Password-reset tokens use Laravel's broker hashing and a separate expert table.
- Forgot-password responses do not reveal whether an account exists.
- Password reset revokes every expert token; password change preserves only the current token.
- Login, registration, verification resend, forgot-password, reset-password, and signed verification are rate-limited before controller work.
- Sensitive values are not logged by this feature. Production must use HTTPS and secure mail transport.

`email_verified_at` and `kyc_status` are not token claims. Future authorization must read their current database values so verification or approval can be revoked or changed without reissuing every token.
