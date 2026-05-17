# Security Review Trigger Rule

## Decision

The Security Review Trigger rule is added to:
- `how-to-system-security.md` — as new Section 41
- `how-to-code-review.md` — as new Section 17
- `how-to-production-readiness.md` — as new Section 12

## Rule Text

### Security Review Trigger Rule

Security review is mandatory when a change touches any of the following areas. If triggered, review evidence MUST include the table below. If not triggered, the governance review must say why. Security review cannot be skipped silently.

Trigger list: authentication, authorization, roles/permissions, sessions, cookies, CSRF, CORS, redirects, user input, request parsing, validation, serialization/deserialization, database query building, filesystem I/O, file upload/download, logging, secrets, hashing, encryption, HTTP client/server, queues and message payloads, cache keys containing user/user-derived data, template/rendering, command/process execution, event payloads crossing boundaries, webhooks, signed URLs, tokens, API keys, password reset flows, rate limiting, tenant isolation, sandboxing, plugin execution, object storage paths, URL generation, proxy/trusted header handling.

Required evidence table:

| Area | Changed? | Risk checked | Finding | Severity | Fix/mitigation | Blocks commit? |
|---|---:|---|---|---|---:|
