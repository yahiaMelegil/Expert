# Administrator Authentication Endpoints

## Endpoint Summary

| Method | Endpoint | Authentication | Purpose |
|---|---|---|---|
| `POST` | `/api/admin/auth/login` | Public, rate-limited | Authenticate an active administrator |
| `GET` | `/api/admin/auth/me` | Admin Bearer Token | Retrieve the current administrator |
| `POST` | `/api/admin/auth/logout` | Admin Bearer Token | Revoke the current token |
| `POST` | `/api/admin/auth/logout-all` | Admin Bearer Token | Revoke all tokens owned by the current administrator |

## Required Headers

All requests:

```http
Accept: application/json
Content-Type: application/json
```

Protected requests:

```http
Authorization: Bearer {admin_access_token}
```

## Login

`POST /api/admin/auth/login`

### Payload

| Field | Type | Required | Rules |
|---|---|---|---|
| `email` | string | Yes | Valid email, maximum 255 characters; normalized to lowercase |
| `password` | string | Yes | Administrator's current password |
| `device_name` | string | No | Maximum 255 characters; defaults to `Admin API Token` |

```bash
curl -X POST http://127.0.0.1:8000/api/admin/auth/login \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "email": "<administrator-email>",
    "password": "<administrator-password>",
    "device_name": "Admin Dashboard"
  }'
```

Success returns `200 OK` with a new Sanctum token restricted to `admin:access`. Existing administrator tokens remain valid until revoked or blocked by the account becoming inactive.

## Get the Authenticated Administrator

`GET /api/admin/auth/me`

```bash
curl http://127.0.0.1:8000/api/admin/auth/me \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer {admin_access_token}'
```

Success returns `200 OK` and the fields required by the administration dashboard: `id`, `name`, `email`, `is_active`, `created_at`, and `updated_at`.

## Logout Current Device

`POST /api/admin/auth/logout`

```bash
curl -X POST http://127.0.0.1:8000/api/admin/auth/logout \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer {admin_access_token}'
```

Success returns `200 OK`. Only the token used in this request is revoked.

## Logout All Devices

`POST /api/admin/auth/logout-all`

```bash
curl -X POST http://127.0.0.1:8000/api/admin/auth/logout-all \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer {admin_access_token}'
```

Success returns `200 OK`. All tokens owned by the authenticated administrator are revoked. Tokens owned by users or other administrators are unaffected.
