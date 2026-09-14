# Responses

## Registration

```json
{
  "status": true,
  "message": "Expert account created successfully. Please verify your email address.",
  "data": {
    "expert": {
      "id": 1,
      "name": "Ahmad Ali",
      "email": "expert@example.com",
      "email_verified_at": null,
      "is_active": true,
      "created_at": "2026-09-12T18:00:00.000000Z",
      "updated_at": "2026-09-12T18:00:00.000000Z"
    },
    "email_verified": false,
    "kyc_status": "not_submitted",
    "token": "1|plain-text-token-returned-once",
    "token_type": "Bearer"
  }
}
```

Login uses the same data shape with the message `Expert logged in successfully.` and status `200`. The token value shown above is illustrative only.

## Me

```json
{
  "status": true,
  "message": "Expert retrieved successfully.",
  "data": {
    "expert": {},
    "email_verified": true,
    "kyc_status": "not_submitted"
  }
}
```

## Invalid Credentials

```json
{
  "status": false,
  "message": "The provided credentials are incorrect."
}
```

## Validation Error

```json
{
  "status": false,
  "message": "The provided data is invalid.",
  "errors": {
    "email": ["The email field must be a valid email address."]
  }
}
```

## Authentication and Authorization

Missing or invalid token (`401`):

```json
{
  "status": false,
  "message": "Unauthenticated."
}
```

Wrong token owner or missing ability (`403`):

```json
{
  "status": false,
  "message": "You are not authorized to access the expert area."
}
```

Inactive expert (`403`):

```json
{
  "status": false,
  "message": "This expert account is currently unavailable."
}
```

Rate limit (`429`):

```json
{
  "status": false,
  "message": "Too many attempts. Please try again later."
}
```
