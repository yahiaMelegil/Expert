# Registration and Login

## Registration

`POST /api/expert/auth/register` accepts `name`, `email`, `password`, `password_confirmation`, and optional `device_name`.

The API trims names and device names, lowercases and trims the email, validates the payload, hashes the password, creates the expert and limited Sanctum token atomically, then sends the verification notification. The initial state is:

```json
{
  "email_verified": false,
  "kyc_status": "not_submitted",
  "is_active": true
}
```

The registration response returns the plain-text token once. The database stores only Sanctum's token hash.

## Login

`POST /api/expert/auth/login` accepts `email`, `password`, and optional `device_name`. Unknown email, wrong password, and inactive account all return the same `401` credentials response.

An unverified expert with `kyc_status=not_submitted` may log in. This is intentional so the future limited dashboard can guide email verification and KYC onboarding. KYC status never changes during authentication.

Every issued token has the `expert:access` ability. If `device_name` is missing or blank, the name `Expert API Token` is used.

## Client Token Handling

Send the token only as a Bearer token:

```http
Authorization: Bearer {expert_access_token}
Accept: application/json
```

Browser clients should minimize token exposure to JavaScript and XSS. Mobile and desktop clients should use the operating system's secure credential store. Never place a token in a URL, analytics event, error report, or application log.
