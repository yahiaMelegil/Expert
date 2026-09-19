# Endpoints

All expert endpoints require:

```http
Authorization: Bearer {expert_token}
Accept: application/json
```

| Method | URL | Purpose |
|---|---|---|
| `GET` | `/api/expert/profile` | Return account prefill, public profile, scopes, availability, and publication checklist |
| `PUT` | `/api/expert/profile` | Update public professional content |
| `POST` | `/api/expert/profile/avatar` | Upload or replace a JPEG, PNG, or WebP avatar up to 5 MB |
| `DELETE` | `/api/expert/profile/avatar` | Remove the current avatar |
| `GET` | `/api/expert/profile/preview` | Preview the public resource without publishing |
| `POST` | `/api/expert/profile/publish` | Publish after all requirements pass |
| `POST` | `/api/expert/profile/unpublish` | Hide the profile voluntarily |
| `GET` | `/api/expert/verified-scopes` | Read all admin-granted scopes |
| `GET` | `/api/expert/availability` | Read availability and capacity |
| `PUT` | `/api/expert/availability` | Update availability and capacity |
| `GET` | `/api/experts/{slug}` | Publicly read a currently eligible published profile |

## Profile update

```json
{
  "professionalTitle": "Senior Legal Consultant",
  "bio": "A factual professional biography of at least eighty characters.",
  "yearsExperience": 12,
  "specialties": ["Commercial contracts", "Corporate governance"],
  "publicLanguages": ["ar", "en"]
}
```

## Availability update

```json
{
  "timezone": "Asia/Amman",
  "serviceModes": ["written_consultation", "document_review"],
  "weeklySchedule": [
    {"day": "mon", "enabled": true, "windows": [{"start": "09:00", "end": "14:00"}]},
    {"day": "tue", "enabled": false, "windows": []}
  ],
  "blackoutDates": [],
  "maxActiveRequests": 4,
  "responseTimeHours": 24,
  "acceptingNewRequests": true
}
```

The schedule may contain up to seven distinct days and three windows per day. An enabled day must have a valid window whose end is after its start.

## Admin KYC approval with scope

`POST /api/admin/kyc/applications/{application}/approve`

```json
{
  "scopes": [
    {
      "domain": "legal",
      "jurisdiction": "Jordan",
      "role": "Legal consultant",
      "serviceTypes": ["written_consultation", "document_review"],
      "languages": ["ar", "en"],
      "validUntil": "2027-09-17"
    }
  ]
}
```

For backward compatibility, omitting `scopes` creates a conservative default scope from the approved KYC application.
