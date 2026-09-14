# Email Verification

## Initial and Resent Notifications

Registration sends `VerifyEmailNotification`. An authenticated expert may request another message through:

```text
POST /api/expert/auth/email/verification-notification
```

The resend endpoint is idempotent for an already verified email and is rate-limited to three requests per minute per expert/IP, with an additional ten-per-minute IP ceiling.

## Signed Link

The notification creates a signed backend URL that expires after 60 minutes:

```text
GET /api/expert/auth/email/verify/{id}/{hash}
```

When `EXPERT_FRONTEND_VERIFY_EMAIL_URL` is configured, the email button opens that frontend page with the signed backend URL in the `verification_url` query parameter. The frontend should read that value and make a `GET` request to it. When the frontend URL is blank, the email links directly to the backend endpoint.

The backend validates the signature, expiration, expert ID, and email hash. Modified, invalid, and expired links return `403`. Reusing a valid link after verification returns a successful idempotent response.

Verification sets only `email_verified_at`. It never activates or approves KYC.

## Future Route Protection

This task exposes only authentication endpoints. Future limited dashboard routes should add verified-email middleware after `auth:sanctum`, `expert`, and `abilities:expert:access`. Future approved features must also check `kyc_status=approved` dynamically.
