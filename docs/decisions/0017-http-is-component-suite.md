# ADR: HTTP is a Component Suite

## Status

**Accepted** - 2026-04-28

## Decision

`components/HTTP/` is a component suite.

It owns inbound HTTP protocol capabilities and these sub-components:

- Request
- Response
- Router
- Middleware
- Session
- Cookies
- Uploads
- URI
- HTTP security middleware

## Rules

`framework/System` owns framework lifecycle.

`components/HTTP` owns web protocol capabilities.

**No HTTP-owned sub-component may also exist as a real top-level component.**

### Allowed:

```
components/HTTP/Session/System/
components/HTTP/middleware/System/
components/HTTP/request/System/
components/HTTP/response/System/
components/HTTP/router/System/
```

### Forbidden:

```
components/session/System/     - unless temporary bridge
components/middleware/System/  - unless temporary bridge
components/request/System/    - unless temporary bridge
components/response/System/    - unless temporary bridge
components/router/System/     - unless temporary bridge
```

## Reason

HTTP request handling is a tightly coupled lifecycle:
- request assembly
- middleware execution
- route matching
- controller execution  
- response shaping
- cookies
- session side effects

Keeping these under one HTTP suite avoids scattered ownership.

## Consequences

- Session becomes HTTP Session.
- Middleware becomes HTTP Middleware.
- Top-level Session and Middleware are removed or reduced to compatibility bridges.
- Future HTTP features should be placed under HTTP suite unless explicitly outbound/client-only.

---

## References

- ToDo.md - HTTP Suite Normalization Plan
- EVIDENCE/http-suite-normalization-truth.md

*Created: 2026-04-28*