# Security - how this works

`Security/*` contains HTTP-level security helpers that are not middleware registry concerns.

- `CsrfTokenManager` owns token generation and validation rules.
- `VerifyCsrfToken` is the direct validation helper for internal flows that already work with the Avax `ServerRequest`.
- `CsrfVerificationMiddleware` in `Middleware/*` is the pipeline-facing PSR-15 wrapper.
