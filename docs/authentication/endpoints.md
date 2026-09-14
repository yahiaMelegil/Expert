# Authentication Endpoints

The examples assume a local base URL of `http://127.0.0.1:8000`.

## Endpoint Summary

| Method | Path | Authentication | Purpose |
|---|---|---|---|
| `POST` | `/api/register` | Public | Create a regular user and token |
| `POST` | `/api/login` | Public | Verify credentials and create a token |
| `GET` | `/api/user` | Bearer token | Retrieve the authenticated user |
| `POST` | `/api/logout` | Bearer token | Revoke the current token |
| `POST` | `/api/logout-all` | Bearer token | Revoke all of the user's tokens |

Registration and login are limited to five requests per minute per client IP.

## Register

`POST /api/register`

### Payload

| Field | Type | Required | Rules |
|---|---|---|---|
| `name` | string | Yes | Maximum 255 characters |
| `email` | string | Yes | Valid, unique email; normalized to lowercase |
| `password` | string | Yes | Minimum 8 characters |
| `password_confirmation` | string | Yes | Must match `password` |
| `device_name` | string | No | Maximum 255 characters; defaults to `API Token` |

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "Abdullah Abu Shamla",
    "email": "abdullah@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_name": "Web App"
  }'
```

Success returns `201 Created` and a newly generated token.

## Login

`POST /api/login`

### Payload

| Field | Type | Required | Rules |
|---|---|---|---|
| `email` | string | Yes | Valid email; normalized to lowercase |
| `password` | string | Yes | User's current password |
| `device_name` | string | No | Maximum 255 characters; defaults to `API Token` |

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "email": "abdullah@example.com",
    "password": "password123",
    "device_name": "Web App"
  }'
```

Success returns `200 OK` and a new independent token. Existing tokens are not revoked by login.

## Get Authenticated User

`GET /api/user`

```bash
curl http://127.0.0.1:8000/api/user \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

Success returns `200 OK`. The user representation includes `id`, `name`, `email`, `email_verified_at`, `created_at`, and `updated_at`.

## Logout Current Device

`POST /api/logout`

```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

Success returns `200 OK`. Only the token supplied in this request is revoked; other device tokens remain valid.

## Logout All Devices

`POST /api/logout-all`

```bash
curl -X POST http://127.0.0.1:8000/api/logout-all \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

Success returns `200 OK`. Every personal access token belonging to the authenticated user, including the current token, is revoked.
