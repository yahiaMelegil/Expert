# Authentication Responses

All authentication endpoints return a predictable JSON envelope. Timestamps use ISO 8601 format.

## Successful Registration

Status: `201 Created`

```json
{
  "status": true,
  "message": "Registration completed successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Abdullah Abu Shamla",
      "email": "abdullah@example.com",
      "email_verified_at": null,
      "created_at": "2026-09-10T12:00:00.000000Z",
      "updated_at": "2026-09-10T12:00:00.000000Z"
    },
    "token": "1|plain-text-sanctum-token",
    "token_type": "Bearer"
  }
}
```

## Successful Login

Status: `200 OK`

The response shape matches successful registration, with this message:

```json
{
  "status": true,
  "message": "Authentication completed successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Abdullah Abu Shamla",
      "email": "abdullah@example.com",
      "email_verified_at": null,
      "created_at": "2026-09-10T12:00:00.000000Z",
      "updated_at": "2026-09-10T12:00:00.000000Z"
    },
    "token": "2|plain-text-sanctum-token",
    "token_type": "Bearer"
  }
}
```

The plain-text token is available only in the response that creates it.

## Authenticated User

Status: `200 OK`

```json
{
  "status": true,
  "message": "Authenticated user retrieved successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Abdullah Abu Shamla",
      "email": "abdullah@example.com",
      "email_verified_at": null,
      "created_at": "2026-09-10T12:00:00.000000Z",
      "updated_at": "2026-09-10T12:00:00.000000Z"
    }
  }
}
```

## Successful Logout

Current token, status `200 OK`:

```json
{
  "status": true,
  "message": "Logged out successfully.",
  "data": null
}
```

All tokens, status `200 OK`:

```json
{
  "status": true,
  "message": "Logged out from all devices successfully.",
  "data": null
}
```

## Validation Error

Status: `422 Unprocessable Content`

```json
{
  "status": false,
  "message": "The provided data is invalid.",
  "errors": {
    "email": [
      "The email field must be a valid email address."
    ]
  }
}
```

## Invalid Credentials

Status: `401 Unauthorized`

```json
{
  "status": false,
  "message": "The provided credentials are incorrect."
}
```

The same message is used for unknown emails and incorrect passwords.

## Missing, Invalid, or Revoked Token

Status: `401 Unauthorized`

```json
{
  "status": false,
  "message": "Unauthenticated."
}
```

## Rate Limit Exceeded

Status: `429 Too Many Requests`

```json
{
  "status": false,
  "message": "Too many attempts. Please try again later."
}
```

The response also includes the standard rate-limit headers supplied by Laravel, including `Retry-After` when applicable.
