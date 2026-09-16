# KYC API Endpoints

All requests should send:

```http
Accept: application/json
Authorization: Bearer {access_token}
```

Expert routes require an active, email-verified `Expert` with the `expert:access` ability. Administrator routes require an active `Admin` with the `admin:access` ability.

## Expert Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/expert/kyc` | Current application, status, and account prefill |
| PUT | `/api/expert/kyc` | Create or update a draft |
| POST | `/api/expert/kyc/documents` | Upload or replace a private document |
| DELETE | `/api/expert/kyc/documents/{document}` | Delete a draft document |
| GET | `/api/expert/kyc/documents/{document}` | Authenticated private download |
| POST | `/api/expert/kyc/submit` | Submit a complete draft |

### Save Draft

```json
{
  "fullName": "Ahmad Ali",
  "country": "JO",
  "language": "ar",
  "domain": "legal",
  "jurisdiction": "Jordan",
  "experiences": [
    {
      "id": 1,
      "jobTitle": "Attorney",
      "organization": "Example Law Firm",
      "from": "2020-01",
      "to": null,
      "current": true,
      "description": "Commercial law experience"
    }
  ],
  "qualifications": [],
  "credentials": []
}
```

Child `id` values are omitted when creating a row and included when updating it. If an array is present, it represents the complete desired set; omitted existing entries are deleted. Omit the whole array when saving an unrelated section.

### Upload Document

Use `multipart/form-data`:

| Field | Required | Notes |
|---|---|---|
| `documentType` | Yes | `identity`, `cv`, `qualification`, `credential`, or `work_sample` |
| `file` | Yes | PDF, JPG, JPEG, or PNG; maximum 10 MB |
| `qualificationId` | For qualification | Must belong to the current draft |
| `credentialId` | For credential | Must belong to the current draft |

Identity, CV, qualification, and credential uploads replace the prior document in the same slot. Up to five work samples are allowed.

## Administrator Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/admin/kyc/applications` | Paginated review queue |
| GET | `/api/admin/kyc/applications/{application}` | Full details and audit history |
| POST | `/api/admin/kyc/applications/{application}/start-review` | `submitted` to `under_review` |
| PUT | `/api/admin/kyc/applications/{application}/documents/{document}/review` | Mark or unmark a document as reviewed |
| GET | `/api/admin/kyc/applications/{application}/documents/{document}` | Protected document download |
| POST | `/api/admin/kyc/applications/{application}/approve` | Approve a reviewed application |
| POST | `/api/admin/kyc/applications/{application}/reject` | Reject with a reason |
| POST | `/api/admin/kyc/applications/{application}/request-information` | Request changes with a reason |

List query parameters are `search`, `status`, `dateFrom`, `dateTo`, `sortBy`, `sortDirection`, and `perPage`. `perPage` is capped at 100. Search covers reference, expert name/email, domain, and jurisdiction.

Document review payload:

```json
{
  "reviewed": true
}
```

Reject or request-information payload:

```json
{
  "reason": "Please upload a clearer identity document."
}
```

## Response Shape

```json
{
  "status": true,
  "message": "Operation completed successfully.",
  "data": {}
}
```

Validation errors use HTTP 422 and field-keyed `errors`. Invalid state transitions use HTTP 409. Missing authentication uses HTTP 401, an invalid account type or unverified expert uses HTTP 403, and rate limiting uses HTTP 429.
