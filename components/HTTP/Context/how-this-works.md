# Context - how this works

`Context/*` owns request-derived environmental facts.

- `HttpContext` reads scheme, host, port, client IP, cookies, and auth headers from the active request first.
- `PhpGlobalsProvider` is the fallback when no request instance exists.
- The boundary is intentionally read-only: it answers runtime context questions and does not mutate request state.
