# Threat Analysis

## Security Boundary

This slice touches framework public/request entrypoint composition but does not change authentication, authorization, token, cookie, session, CSRF, SQL, filesystem, or serialization behavior.

## Assets

- request isolation
- dispatch pipeline integrity
- public API stability
- error message safety

## Threats Considered

- stale runtime object graph in long-lived workers
- hidden fallback dependency construction
- request data leakage through logs/errors
- public API behavior break

## Mitigations

- lazy dispatcher construction removed from `App`
- dispatcher graph assembled in Configuration builder
- no new log/error output
- focused public contract tests required

## Negative Tests

No new security negative test is required because no security decision or untrusted-input validation behavior changes.

## Classification

LOW security impact for Slice A.

No HIGH/BLOCKER security issue introduced.
