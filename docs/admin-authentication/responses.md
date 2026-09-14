# Administrator Authentication Responses

Administrator authentication uses the same JSON envelope as regular-user authentication.

## Successful Login

Status: `200 OK`

```json
{
  "status": true,
  "message": "Administrator logged in successfully.",
  "data": {
    "admin": {
      "id": 1,
      "name": "Administrator",
      "email": "<administrator-email>",
      "is_active": true,
      "created_at": "2026-09-11T12:00:00.000000Z",
      "updated_at": "2026-09-11T12:00:00.000000Z"
    },
    "token": "{plain_text_token_returned_once}",
    "token_type": "Bearer"
  }
}
```

## Authenticated Administrator

Status: `200 OK`

```json
{
  "status": true,
  "message": "Administrator retrieved successfully.",
  "data": {
    "admin": {
      "id": 1,
      "name": "Administrator",
      "email": "<administrator-email>",
      "is_active": true,
      "created_at": "2026-09-11T12:00:00.000000Z",
      "updated_at": "2026-09-11T12:00:00.000000Z"
    }
  }
}
```

## Successful Logout

Current token:

```json
{
  "status": true,
  "message": "Administrator logged out successfully.",
  "data": null
}
```

All tokens:

```json
{
  "status": true,
  "message": "Administrator logged out from all devices successfully.",
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

## Invalid Credentials or Inactive Account

Status: `401 Unauthorized`

```json
{
  "status": false,
  "message": "The provided credentials are incorrect."
}
```

The response does not reveal whether the email exists, the password is incorrect, or the administrator is inactive.

## Missing, Invalid, or Revoked Token

Status: `401 Unauthorized`

```json
{
  "status": false,
  "message": "Unauthenticated."
}
```

## Unauthorized Administration Access

Status: `403 Forbidden`

```json
{
  "status": false,
  "message": "You are not authorized to access the administration area."
}
```

This response is returned for a regular-user token, an inactive administrator, or an administrator token without `admin:access`.

## Rate Limit Exceeded

Status: `429 Too Many Requests`

```json
{
  "status": false,
  "message": "Too many attempts. Please try again later."
}
```
