# Password Management

## Forgot Password

`POST /api/expert/auth/forgot-password` accepts a normalized email and uses the `experts` password broker. It always returns the same success response whether or not an expert exists. Reset tokens are never returned by the API or written to logs.

The email opens `EXPERT_FRONTEND_RESET_PASSWORD_URL` with `token` and `email` query parameters. The frontend submits those values with the new password to the reset endpoint.

## Reset Password

`POST /api/expert/auth/reset-password` requires:

```json
{
  "email": "expert@example.com",
  "token": "reset-token",
  "password": "newPassword123",
  "password_confirmation": "newPassword123"
}
```

The expert broker validates the token and its 60-minute lifetime. A successful reset hashes the password, rotates `remember_token`, removes the reset token, and revokes every Sanctum token owned by that expert. The expert must log in again.

## Change Password

`PUT /api/expert/auth/password` requires a valid expert Bearer token and accepts:

```json
{
  "current_password": "oldPassword123",
  "password": "newPassword123",
  "password_confirmation": "newPassword123"
}
```

The current password is checked securely. On success, the password is hashed, `remember_token` is rotated, and all other expert tokens are revoked. The token used for the request remains valid, so the current device is not unexpectedly signed out. No new token is returned.
