# Endpoints

All responses are JSON. Protected endpoints require `Accept: application/json` and `Authorization: Bearer {token}`.

| Method | Endpoint | Access | Success |
| --- | --- | --- | --- |
| `POST` | `/api/expert/auth/register` | Public, rate-limited | `201` |
| `POST` | `/api/expert/auth/login` | Public, rate-limited | `200` |
| `GET` | `/api/expert/auth/me` | Expert token | `200` |
| `POST` | `/api/expert/auth/email/verification-notification` | Expert token, rate-limited | `200` |
| `GET` | `/api/expert/auth/email/verify/{id}/{hash}` | Signed, expiring URL | `200` |
| `POST` | `/api/expert/auth/forgot-password` | Public, rate-limited | `200` |
| `POST` | `/api/expert/auth/reset-password` | Public, rate-limited | `200` |
| `PUT` | `/api/expert/auth/password` | Expert token | `200` |
| `POST` | `/api/expert/auth/logout` | Expert token | `200` |
| `POST` | `/api/expert/auth/logout-all` | Expert token | `200` |

## Register

```http
POST /api/expert/auth/register
Accept: application/json
Content-Type: application/json
```

```json
{
  "name": "Ahmad Ali",
  "email": "expert@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "device_name": "Expert Dashboard"
}
```

## Login

```json
{
  "email": "expert@example.com",
  "password": "password123",
  "device_name": "Expert Dashboard"
}
```

## Forgot Password

```json
{
  "email": "expert@example.com"
}
```

## Reset Password

```json
{
  "email": "expert@example.com",
  "token": "token-from-email-link",
  "password": "newPassword123",
  "password_confirmation": "newPassword123"
}
```

## Change Password

```json
{
  "current_password": "password123",
  "password": "newPassword123",
  "password_confirmation": "newPassword123"
}
```

## Rate Limits

| Flow | Primary limit | Additional IP ceiling |
| --- | --- | --- |
| Registration | 5/minute per normalized email + IP | 20/minute |
| Login | 5/minute per normalized email + IP | 30/minute |
| Verification resend | 3/minute per expert + IP | 10/minute |
| Forgot password | 3/minute per normalized email + IP | 10/minute |
| Reset password | 5/minute per normalized email + IP | 20/minute |
| Signed verification | 10/minute per request origin | — |
