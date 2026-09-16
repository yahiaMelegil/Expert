# KYC Security

## Identity and Authorization

- Expert ownership is always derived from the authenticated Sanctum token.
- The API never accepts an `expert_id` from an expert request.
- Expert routes verify model type, account availability, token ability, and email verification.
- Administrator routes verify model type, account availability, and token ability.
- Document downloads recheck application ownership or administrator authorization on every request.
- Draft applications are not visible through administrator endpoints.

## Private File Storage

- The default `kyc` disk points to `storage/app/private/kyc`.
- No public storage link is used.
- Stored names are random UUID values and do not trust the client filename.
- Validation checks the detected file type and enforces PDF/JPG/JPEG/PNG with a 10 MB maximum.
- API responses expose safe metadata and protected download URLs only; disk names, internal paths, and checksums are hidden.
- Downloads use `Cache-Control: private, no-store`.
- Replaced files are removed only when no historical application still references the same private object.

Antivirus or content-disarm scanning is not included because the project does not currently provide a scanning service. A scanner can later be inserted before the application becomes reviewable.

## State and Audit Integrity

- The client cannot mass-assign KYC status, review fields, administrator IDs, timestamps, or expert coarse status.
- Decisions and submissions use transactions and row locks.
- Every status change writes an audit record with actor type, actor ID, reason, and timestamp.
- A verified attempt is immutable.
- Rejected or change-requested attempts remain unchanged; a later edit creates a new attempt.

Production must use HTTPS, protect application backups, and restrict operating-system access to `storage/app/private`.
