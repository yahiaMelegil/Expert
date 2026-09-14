# Complete Authentication Endpoint Index

All requests should send `Accept: application/json`. JSON requests should also send `Content-Type: application/json`. Protected requests require `Authorization: Bearer {token}`.

## Regular Users

| Method | Path | Access | Success |
| --- | --- | --- | --- |
| `POST` | `/api/register` | Public, rate-limited | `201` |
| `POST` | `/api/login` | Public, rate-limited | `200` |
| `GET` | `/api/user` | User token | `200` |
| `POST` | `/api/logout` | User token | `200` |
| `POST` | `/api/logout-all` | User token | `200` |

## Administrators

| Method | Path | Access | Success |
| --- | --- | --- | --- |
| `POST` | `/api/admin/auth/login` | Public, rate-limited | `200` |
| `GET` | `/api/admin/auth/me` | Active admin token | `200` |
| `POST` | `/api/admin/auth/logout` | Active admin token | `200` |
| `POST` | `/api/admin/auth/logout-all` | Active admin token | `200` |

There is deliberately no public administrator-registration endpoint. The first administrator is created with `AdminSeeder`.

## Experts

| Method | Path | Access | Success |
| --- | --- | --- | --- |
| `POST` | `/api/expert/auth/register` | Public, rate-limited | `201` |
| `POST` | `/api/expert/auth/login` | Public, rate-limited | `200` |
| `GET` | `/api/expert/auth/me` | Active expert token | `200` |
| `POST` | `/api/expert/auth/email/verification-notification` | Active expert token, rate-limited | `200` |
| `GET` | `/api/expert/auth/email/verify/{id}/{hash}` | Signed, expiring URL | `200` |
| `POST` | `/api/expert/auth/forgot-password` | Public, rate-limited | `200` |
| `POST` | `/api/expert/auth/reset-password` | Public, rate-limited | `200` |
| `PUT` | `/api/expert/auth/password` | Active expert token | `200` |
| `POST` | `/api/expert/auth/logout` | Active expert token | `200` |
| `POST` | `/api/expert/auth/logout-all` | Active expert token | `200` |

Payloads, response examples, validation rules, and rate-limit details remain in each feature's endpoint and response documentation.
