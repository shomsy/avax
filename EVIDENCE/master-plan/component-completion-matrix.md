# AvaX Component Completion Matrix

Date: 2026-05-06
Stage: 04 - Component Completion
Status: RED / MATRIX CURRENT / COMPONENTS NOT COMPLETE

## Completion Rule

A component is complete only when it has evidence for:

```text
[ ] correct suite
[ ] canonical namespace
[ ] meaningful PublicSurface if public API exists
[ ] meaningful Capabilities
[ ] meaningful Flows if orchestration exists
[ ] meaningful Configuration if assembly/config exists
[ ] meaningful Foundation only for local values/failures/primitives
[ ] no placeholder classes
[ ] no describeResponsibility-only classes
[ ] no duplicate owner
[ ] no stale namespace
[ ] how-this-works.md updated
[ ] tests planned or added under root tests/
```

No component is marked COMPLETE in this matrix unless current validation and component-level proof support it.

## Global Evidence Used

- `composer validate --no-check-publish`: PASS in V1-03 and Stage 03.
- `composer dump-autoload -o`: PASS, 6519 classes, 0 PSR-4 skips.
- V1-03 required structural checkers: PASS.
- V1-03 targeted PHPStan: PASS for `framework/System`, `Application/Cache`, `HTTP/Request`, `HTTP/Response`, and
  `DataStack/Database`.
- PHPUnit: PASS, 205 tests, 1687 assertions, 1 skipped.
- Public API classification policy and matrix: present and validated.

## Global Completion Risks

- Full `vendor/bin/phpstan analyse framework components tests ...` is not current evidence.
- Several components contain TODO/placeholder-style risk markers.
- `describeResponsibility()`-only style appears in Operations/ApplicationWorkflow saga classes and must be reviewed
  before completion.
- Component-local docs and tests are uneven.
- V2/V3/V4 implementation remains locked.

## Current Component Matrix

| Suite          | Component           | Classification         | Status                              | Evidence / Risk                                                                                                | Next Action                                                        |
|----------------|---------------------|------------------------|-------------------------------------|----------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------|
| Application    | Cache               | V1 component           | PARTIAL / STATIC GREEN              | Targeted PHPStan green; PHPUnit green; completion proof still incomplete.                                      | Complete component proof checklist.                                |
| Application    | Config              | V1 component           | PARTIAL                             | PublicSurface and tests exist; completion evidence not current.                                                | Audit PublicSurface, capabilities, docs, tests.                    |
| Application    | Container           | V1 component           | PARTIAL                             | Core behavior exists; component-local tests still produce classified broken refs.                              | Classify test-local fixture refs or move proof to root tests.      |
| Application    | DateTime            | V1 component           | PARTIAL                             | PublicSurface and unit tests exist; completion evidence not current.                                           | Audit docs and runtime-safe fake clock story.                      |
| Application    | Facade              | V1 component           | PARTIAL / REPAIR PENDING VALIDATION | Accessor property type drift was repaired after PHPStan exposed it; rerun was blocked by approval usage limit. | Rerun targeted PHPStan and facade tests when tooling is available. |
| Application    | FeatureFlags        | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit safe defaults and tests.                                     |
| Application    | Filesystem          | V1 component           | PARTIAL                             | PublicSurface exists; security/path-traversal proof not current.                                               | Audit filesystem security and tests.                               |
| Application    | Localization        | V1 component           | PARTIAL                             | Component exists; completion evidence not current.                                                             | Audit ownership and tests.                                         |
| Application    | Pipeline            | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit pipeline execution tests.                                    |
| Application    | System              | suite-level component  | PARTIAL                             | Top-level Application component exists with canonical lanes.                                                   | Verify whether this is intentional suite surface.                  |
| Application    | Text                | V1 component           | PARTIAL                             | PublicSurface and tests exist; completion evidence not current.                                                | Audit docs and public contract.                                    |
| Application    | Validation          | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit negative validation tests.                                   |
| CLI            | Console             | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit command contract tests.                                      |
| CLI            | System              | suite-level component  | PARTIAL                             | Top-level CLI component exists with canonical lanes.                                                           | Verify suite-level ownership.                                      |
| DataStack      | Data                | V1 component           | PARTIAL                             | PublicSurface and tests exist; completion evidence not current.                                                | Audit collection/data-shape proof.                                 |
| DataStack      | Database            | V1 component           | PARTIAL / STATIC GREEN              | Targeted PHPStan green; SQLite and query tests pass in full suite.                                             | Complete database component proof checklist.                       |
| DataStack      | Persistence         | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit failure model and tests.                                     |
| DataStack      | System              | suite-level component  | PARTIAL                             | Top-level DataStack component exists with canonical lanes.                                                     | Verify suite-level ownership.                                      |
| DeveloperTools | CodeGeneration      | V1 / tooling component | PARTIAL                             | Capabilities exist; completion evidence not current.                                                           | Audit generated-code safety and forbidden folder rules.            |
| DeveloperTools | Diagnostics         | V1 / tooling component | PARTIAL                             | PublicSurface exists; diagnostic redaction proof not current.                                                  | Audit diagnostics security evidence.                               |
| DeveloperTools | DumpDebugger        | V1 / tooling component | PARTIAL                             | PublicSurface exists; broken refs classify missing Dump as stale/non-production risk.                          | Verify canonical owner or classify compatibility.                  |
| DeveloperTools | Dx                  | V1 / tooling component | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit public contract and tests.                                   |
| DeveloperTools | System              | suite-level component  | PARTIAL                             | Top-level DeveloperTools component exists with canonical lanes.                                                | Verify suite-level ownership.                                      |
| DeveloperTools | Testing             | V1 / tooling component | PARTIAL                             | Capabilities exist; completion evidence not current.                                                           | Audit test tooling ownership.                                      |
| HTTP           | AfterResponse       | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit lifecycle and tests.                                         |
| HTTP           | ApiVersioning       | V1 component           | PARTIAL                             | PublicSurface and tests exist; completion evidence not current.                                                | Audit compatibility/version tests.                                 |
| HTTP           | Client              | V1 component           | PARTIAL                             | PublicSurface exists; external I/O timeout proof not current.                                                  | Audit security/performance boundaries.                             |
| HTTP           | ContentNegotiation  | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit formatter tests.                                             |
| HTTP           | Context             | V1 component           | PARTIAL                             | PublicSurface exists; request-scope proof not current.                                                         | Audit runtime leak tests.                                          |
| HTTP           | Dispatcher          | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit controller dispatch tests.                                   |
| HTTP           | Middleware          | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit middleware provider and pipeline proof.                      |
| HTTP           | Request             | V1 component           | PARTIAL / STATIC GREEN              | Targeted PHPStan green; request tests pass in full suite.                                                      | Complete request component proof checklist.                        |
| HTTP           | Response            | V1 component           | PARTIAL / STATIC GREEN              | Targeted PHPStan green; response provider/failure drift repaired.                                              | Complete response component proof checklist.                       |
| HTTP           | Router              | V1 component           | PARTIAL                             | PublicSurface and integration tests exist; completion evidence not current.                                    | Audit router hardening proof.                                      |
| HTTP           | Security            | V1 component           | PARTIAL                             | PublicSurface exists; security proof not current.                                                              | Audit CSRF/signed URL/header tests.                                |
| HTTP           | Session             | V1 component           | PARTIAL                             | PublicSurface exists; session isolation tests pass in broader suite.                                           | Audit session security proof.                                      |
| HTTP           | System              | suite-level component  | PARTIAL                             | Top-level HTTP component exists with canonical lanes.                                                          | Verify suite-level ownership.                                      |
| HTTP           | URI                 | V1 component           | PARTIAL                             | Capabilities exist; no PublicSurface directory found.                                                          | Audit intended public API.                                         |
| Identity       | Access              | V1 component           | PARTIAL                             | PublicSurface exists; authorization proof not current.                                                         | Audit object-level authorization tests.                            |
| Identity       | Auth                | V1 component           | PARTIAL                             | PublicSurface exists; component-local docs/tests contain archived material.                                    | Audit auth security and canonical tests.                           |
| Identity       | Credentials         | V1 component           | PARTIAL                             | Capabilities exist; no PublicSurface directory found.                                                          | Audit MFA/credential public boundary.                              |
| Identity       | ExternalIdentity    | V1 component           | PARTIAL                             | Capabilities exist; completion evidence not current.                                                           | Audit external identity boundaries.                                |
| Identity       | Security            | V1 component           | PARTIAL                             | PublicSurface exists; security proof not current.                                                              | Audit encryption/security tests.                                   |
| Identity       | System              | suite-level component  | PARTIAL                             | Top-level Identity component exists with canonical lanes.                                                      | Verify suite-level ownership.                                      |
| Identity       | Tenancy             | V1 component           | PARTIAL                             | PublicSurface exists; tenant isolation proof not current.                                                      | Audit tenant boundary tests.                                       |
| Identity       | Tokens              | V1 component           | PARTIAL                             | PublicSurface exists; token expiry/security proof not current.                                                 | Audit token security tests.                                        |
| Operations     | ApplicationWorkflow | V1 component           | PARTIAL / RISK                      | `describeResponsibility()` appears in saga classes; completion cannot be green.                                | Replace or justify describeResponsibility-only classes.            |
| Operations     | Concurrency         | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit concurrency tests.                                           |
| Operations     | Delivery            | V2 draft               | LOCKED / PARTIAL                    | Existing code present; V2 implementation remains locked.                                                       | Planning/classification only.                                      |
| Operations     | Events              | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit event tests and API.                                         |
| Operations     | Filesystem          | V1 component           | PARTIAL                             | Capabilities exist; completion evidence not current.                                                           | Audit ownership versus Application/Filesystem.                     |
| Operations     | Logging             | V1 component           | PARTIAL                             | PublicSurface exists; redaction proof not current.                                                             | Audit logging security.                                            |
| Operations     | Mail                | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit mail queue/failure tests.                                    |
| Operations     | MemoryLifecycle     | V2 draft               | LOCKED / PARTIAL                    | Existing code present; V2 implementation remains locked.                                                       | Planning/classification only.                                      |
| Operations     | MessageBus          | V1 component           | PARTIAL                             | PublicSurface exists; duplicate inline definitions previously repaired.                                        | Audit bus tests.                                                   |
| Operations     | Notifications       | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit notification tests.                                          |
| Operations     | Observability       | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit metrics/log/trace contract.                                  |
| Operations     | Queue               | V1 component           | PARTIAL                             | PublicSurface exists; Redis external dependency refs classified.                                               | Audit queue tests and Redis boundary.                              |
| Operations     | Realtime            | V2 draft               | LOCKED / PARTIAL                    | Existing code present; V2 implementation remains locked.                                                       | Planning/classification only.                                      |
| Operations     | Resilience          | V1 component           | PARTIAL                             | PublicSurface exists; external Redis refs classified.                                                          | Audit idempotency/rate limiter tests.                              |
| Operations     | RuntimeSupervision  | V2 draft               | LOCKED / PARTIAL                    | Existing public surface; V2 implementation remains locked.                                                     | Planning/classification only.                                      |
| Operations     | Scheduler           | V1 component           | PARTIAL                             | PublicSurface exists; completion evidence not current.                                                         | Audit scheduler tests.                                             |
| Operations     | System              | suite-level component  | PARTIAL                             | Top-level Operations component exists with canonical lanes.                                                    | Verify suite-level ownership.                                      |
| Operations     | Tasks               | V1 component           | PARTIAL                             | Capabilities exist; no PublicSurface directory found.                                                          | Audit ownership versus Queue.                                      |
| Presentation   | System              | suite-level component  | PARTIAL                             | Top-level Presentation component exists with canonical lanes.                                                  | Verify suite-level ownership.                                      |
| Presentation   | View                | V1 component           | PARTIAL                             | PublicSurface exists; vendor isolation proof not current.                                                      | Audit view rendering and escaping tests.                           |
| Security       | Hashing             | V1 component           | PARTIAL                             | Capabilities exist; no PublicSurface directory found.                                                          | Audit hashing public boundary.                                     |
| Security       | Secrets             | V1 component           | PARTIAL                             | PublicSurface exists; secret redaction proof not current.                                                      | Audit secret handling tests.                                       |
| Security       | System              | suite-level component  | PARTIAL                             | Top-level Security component exists with canonical lanes.                                                      | Verify suite-level ownership.                                      |

## Priority Order

Follow the Stage 04 priority from production-readiness governance:

1. Application/Facade
2. Application/FeatureFlags
3. Application/Pipeline
4. CLI/Console normalization
5. HTTP incomplete components
6. Identity incomplete components
7. Security components
8. Operations incomplete components
9. DeveloperTools incomplete components
10. Presentation/View vendor isolation

## Verdict

Stage 04 is RED / IN PROGRESS.

The matrix is current enough to guide the next repair, but component completion is not green.
