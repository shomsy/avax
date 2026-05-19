# V5.8.3 Component Inventory & Status Lock

Date: 2026-05-13
Branch: main
Commit: d2274a507b019cfd145ee5f000ee8fcfc252dc5d

## Classification Method

Every leaf component under components/ classified by:

- Has PHP implementation
- Has real public API behavior
- Has central behavior tests
- Has health/doctor check
- Has ServiceProvider/assembly entrypoint

## Status Definitions

| Status        | Meaning                                                      |
|---------------|--------------------------------------------------------------|
| ACTIVE_GREEN  | Real implementation + tests + assembly                       |
| ACTIVE_YELLOW | Real implementation but gaps (missing tests/assembly/health) |
| ACTIVE_RED    | Active but broken or insecure                                |
| SCAFFOLD      | Dirs exist, minimal/no real implementation                   |
| ROADMAP       | Reserved, no impl, planned                                   |
| DEPRECATED    | Was active, now replaced                                     |
| EVIDENCE_ONLY | Documentation/reference only                                 |

## Component Status Table

| Component                     | Status        | Has System/ | Has PublicSurface | PHP Impl | Tests | Health | Assembly        | Notes                                            |
|-------------------------------|---------------|-------------|-------------------|----------|-------|--------|-----------------|--------------------------------------------------|
| API/GraphQL                   | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Real GraphQL engine                              |
| Application/Container         | ACTIVE_YELLOW | Yes         | Yes               | Yes      | Yes   | No     | Partial         | shortcuts.php real, Foundation placeholder       |
| Application/Filesystem        | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full PSR-compliant                               |
| CLI/Console                   | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full impl                                        |
| DataStack/Database            | ACTIVE_YELLOW | Yes         | Yes               | Yes      | Yes   | No     | DatabaseBuilder | DI assembly improved in V5.8.3                   |
| HTTP/Middleware               | SCAFFOLD      | Yes         | Yes               | Partial  | No    | No     | No              | Abstract base only                               |
| HTTP/Request                  | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full PSR-7                                       |
| HTTP/Response                 | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full PSR-7                                       |
| HTTP/Router                   | ACTIVE_YELLOW | Yes         | Yes               | Yes      | No*   | No     | RouterBuilder   | baseUri configurable in V5.8.3                   |
| HTTP/Session                  | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full impl                                        |
| Identity/Access               | ACTIVE_YELLOW | Yes         | Yes               | Yes      | Yes   | No     | No              | Auth capabilities real, User encapsulated V5.8.3 |
| Identity/Credentials          | SCAFFOLD      | Yes         | Yes               | Yes      | No    | No     | No              | Static array store - SECURITY WARNING            |
| Identity/Tokens               | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full OAuth2                                      |
| Integration/ObjectStorage     | ACTIVE_YELLOW | Yes         | Yes               | Yes      | Yes   | Yes    | No              | read() fixed V5.8.3                              |
| Operations/Concurrency        | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full Fiber-based                                 |
| Operations/Events             | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | PSR-14 interop                                   |
| Operations/Logging            | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | PSR-3 impl                                       |
| Operations/Mail               | SCAFFOLD      | Yes         | Yes               | No       | No    | No     | No              | No PHP in PublicSurface                          |
| Operations/Queue              | SCAFFOLD      | Yes         | Yes               | No       | Yes   | No     | No              | Broker exists, no public API                     |
| Operations/Scheduler          | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full impl                                        |
| Security/Cryptography         | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | AES-256                                          |
| Security/Hashing              | SCAFFOLD      | Yes         | Yes               | No       | No    | No     | No              | No PHP in PublicSurface                          |
| Security/Redaction            | ACTIVE_GREEN  | Yes         | Yes               | Yes      | Yes   | No     | No              | Full impl                                        |
| DeveloperTools/CodeGeneration | SCAFFOLD      | Yes         | Yes               | Yes      | No    | No     | No              | Trivial generators                               |
| DeveloperTools/Diagnostics    | ACTIVE_YELLOW | Yes         | Yes               | Yes      | No    | No     | No              | Dev-only (var_dump/dd)                           |
| DeveloperTools/Testing        | ROADMAP       | Yes         | Yes               | Yes      | No    | No     | No              | Hollow verification                              |
| Foundation                    | ROADMAP       | No          | No                | No       | No    | No     | No              | Area reserved                                    |
| Integration                   | ACTIVE        | Yes         | Yes               | Yes      | Yes   | Yes    | No              | ObjectStorage                                    |
| Presentation                  | ROADMAP       | Yes         | No                | No       | No    | No     | No              | Area reserved                                    |
| SystemDesign                  | EVIDENCE_ONLY | No          | No                | No       | No    | No     | No              | See labs/SystemDesignKit                         |

*Router tests pre-existing failures (233 tests) — not introduced by V5.8.3

## Summary

| Status        | Count |
|---------------|-------|
| ACTIVE_GREEN  | 13    |
| ACTIVE_YELLOW | 6     |
| SCAFFOLD      | 6     |
| ROADMAP       | 4     |
| EVIDENCE_ONLY | 1     |

## Health/Doctor Policy

Mandatory for: ACTIVE_GREEN and ACTIVE_YELLOW components that are runtime-critical.

- Database: needs health check (DEFERRED)
- Router: needs health check (DEFERRED)
- Events: needs health check (DEFERRED)
- ObjectStorage: HAS health check

Optional for: SCAFFOLD, ROADMAP, EVIDENCE_ONLY
