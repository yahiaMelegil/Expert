# KYC Architecture

## Data Model

| Table | Purpose |
|---|---|
| `experts` | Expert account, registration context, and coarse KYC status |
| `expert_kyc_applications` | One immutable review attempt after submission, its retry lineage, and structured review feedback |
| `expert_kyc_experiences` | Experience entries belonging to an attempt |
| `expert_kyc_qualifications` | Qualification entries belonging to an attempt |
| `expert_kyc_credentials` | Certificate and license entries belonging to an attempt |
| `expert_kyc_documents` | Private file metadata and per-document review state |
| `expert_kyc_status_histories` | Append-only status transition audit trail |

An expert may have multiple attempts, identified by the unique `(expert_id, attempt_number)` pair. An application owns its child records and documents. Review decisions store the administrator ID and decision timestamp.

## Status Transitions

```text
draft -> submitted -> under_review -> verified
                                  -> rejected -> new draft attempt
                                  -> needs_information -> new draft attempt
```

- Experts can edit only `draft` applications.
- Only experts can submit a draft.
- Administrators explicitly start the review; viewing details never changes state.
- Approval, rejection, and information requests are allowed only from `under_review`.
- Approval requires every document to have been reviewed.
- Rejection and information requests require a reason.
- Information requests also require one or more structured change items.
- `verified` is terminal.
- Sensitive transitions use a database transaction and `lockForUpdate()` to prevent conflicting decisions.

## Account Data and Snapshots

The first GET returns `name`, `email`, `country`, `language`, and `domain` from the authenticated expert account as prefill data. The client never submits an `expert_id`.

When the first draft is saved, the application stores:

- `full_name`
- `email_snapshot`
- `country`
- `language`
- `domain`
- `jurisdiction`

This snapshot preserves the reviewed identity and scope even if the account changes later. The expert may edit the KYC-specific copy while the attempt is a draft. Email always comes from the authenticated account and is not accepted from the KYC payload.

## Retry Behavior

The first save after `rejected` or `needs_information` creates a new draft attempt by copying the previous attempt's structured data and document references. The new attempt stores `source_application_id`, while the original attempt retains its decision reason and `requested_changes`. The API resolves this lineage into `reviewFeedback`, so instructions remain visible throughout correction. Replacing a copied document does not remove the physical file while an earlier attempt still references it. This preserves the audit trail without duplicating file bytes.

The KYC workflow intentionally does not modify authentication eligibility. `is_active` remains the account availability control, and experts awaiting review can still authenticate.
