# AvaX Component Completion Matrix

Date: 2026-05-03  
Status: RED  
Source: Stage 04 from `EVIDENCE/avax-master-development-plan-v1.md`

## Completion Rules

A component may be called complete only when it has:

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

V2 platform engines additionally require the 15-point engine-grade standard from
`.agents/how-to/how-to-design-components.md`.

## Current Matrix

| Suite          | Component           | Classification      | Status           | Required Action                                                             |
|----------------|---------------------|---------------------|------------------|-----------------------------------------------------------------------------|
| Application    | Cache               | V1 component        | RED              | Finish PHPStan, autoload, tests, docs, and stale namespace repair.          |
| Application    | Container           | V1 component        | RED              | Repair legacy tests and namespace drift in test layer.                      |
| Application    | Config              | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Application    | DateTime            | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Application    | Facade              | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Application    | FeatureFlags        | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Application    | Filesystem          | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Application    | Pipeline            | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Application    | Text                | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Application    | Validation          | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| HTTP           | Router              | V1 component        | RED              | Repair stale tests/imports and missing legacy route-definition assumptions. |
| HTTP           | Request             | V1 component        | RED              | Repair stale tests/imports and canonical test coverage.                     |
| HTTP           | Response            | V1 component        | RED              | Repair stale public class references and tests.                             |
| HTTP           | Middleware          | V1 component        | RED              | Repair test named-argument and namespace drift.                             |
| HTTP           | Session             | V1 component        | RED              | Repair constructor drift in tests.                                          |
| HTTP           | Security            | V1 component        | RED              | Fix namespace ownership drift under `Security\\System\\System\\System`.     |
| CLI            | Commands            | non-canonical owner | RED              | Fold into `CLI/Console`.                                                    |
| CLI            | UI                  | non-canonical owner | RED              | Fold into `CLI/Console`.                                                    |
| CLI            | Console             | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| DataStack      | Data                | V1 component        | RED              | Repair autoload skips and class-map evidence.                               |
| DataStack      | Database            | V1 component        | RED              | Repair autoload skips and namespace drift.                                  |
| DataStack      | Persistence         | V1 component        | RED              | Repair multi-class files and autoload skips.                                |
| Identity       | Auth                | V1 component        | RED              | Repair component-local tests/examples and autoload skips.                   |
| Identity       | Access              | V1 component        | RED              | Repair nested capability namespace drift.                                   |
| Identity       | Tokens              | V1 component        | RED              | Repair nested capability namespace drift.                                   |
| Identity       | Credentials         | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Identity       | ExternalIdentity    | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Identity       | Tenancy             | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Security       | System              | taxonomy blocker    | RED              | Restructure into canonical security components or approved suite shape.     |
| Operations     | ApplicationWorkflow | V1 component        | YELLOW           | PublicSurface checker passes; still needs full component proof.             |
| Operations     | Events              | V1 component        | RED              | Repair constructor drift and static-analysis errors in tests.               |
| Operations     | Monitoring          | non-canonical owner | RED              | Fold into `Operations/Observability`.                                       |
| Operations     | Observability       | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| Operations     | RuntimeSupervision  | V2 draft            | LOCKED           | Wait for V1 Kernel Green.                                                   |
| Operations     | MemoryLifecycle     | V2 draft            | LOCKED           | Wait for V1 Kernel Green.                                                   |
| Operations     | Delivery            | V2 draft            | LOCKED           | Wait for V1 Kernel Green.                                                   |
| Presentation   | View                | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| DeveloperTools | Diagnostics         | V1 component        | RED              | Repair component tests and docs evidence.                                   |
| DeveloperTools | CodeGeneration      | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| DeveloperTools | Testing             | V1 component        | UNKNOWN          | Audit completion evidence.                                                  |
| API            | Contracts           | V2 draft            | LOCKED / PARTIAL | Existing draft normalized; not V2 complete.                                 |
| Integration    | ObjectStorage       | V2 draft            | LOCKED           | Wait for V1 Kernel Green.                                                   |
| DataLayer      | multiple            | non-canonical owner | RED              | Fold into `DataStack`.                                                      |
| DependencyMap  | System              | non-canonical owner | RED              | Classify or move to canonical suite/tooling.                                |
| Documentation  | Api                 | non-canonical owner | RED              | Move documentation truth to `docs/` or classify as code-generation tooling. |
| Performance    | System              | later-stage concern | RED              | Move to `benchmarks/` or Stage 14 scope.                                    |
| Server         | System              | non-canonical owner | RED              | Fold into framework runtime or approved suite.                              |

## Verdict

Stage 04 is RED. It must not be skipped again.
