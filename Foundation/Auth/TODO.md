# TODO: Avax Auth Refactor

Canonical active implementation queue for the architectural upgrade.

## Entries

- [ ] **AUTH-REF-001 | Fluent AuthBuilder | P0**
    - Problem: Static factories in `Auth.php` are rigid.
    - Status: todo
    - Estimate: 2h
    - Link: Configuration/AuthBuilder.php

- [ ] **AUTH-REF-002 | Authorization Layer Flattening | P1**
    - Problem: Excessive nesting (Requirement -> CheckRequirement -> Action).
    - Status: todo
    - Estimate: 1h
    - Link: RequireAuthentication/, RequireRole/, RequirePermission/

- [ ] **AUTH-REF-003 | Brute Force Protection Flow | P1**
    - Problem: Naming is technical (RateLimit) instead of flow-first.
    - Status: todo
    - Estimate: 1h
    - Link: Login/RateLimit/ -> Login/BruteForceProtection/

- [ ] **AUTH-REF-004 | Complete JWT Identity | P1**
    - Problem: Missing implementation for `JwtIdentity`.
    - Status: todo
    - Estimate: 1h
    - Link: JwtIdentity/JwtIdentity.php

---
Updated: 2026-04-06 14:50 CET
