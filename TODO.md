# AvaX Production Roadmap

Status: V1 KERNEL GREEN
Execution control: `EVIDENCE/EXECUTION.md` is the active stage lock.
Next active stage: Engine Implementation Phase

## 1. Current Truth
```text
V1 Kernel Green: PROVEN
V2 Implementation: PARTIALLY IMPLEMENTED (API Surface/OpenAPI/GraphQL slices present; current validation blocked)
V3 Implementation: LOCKED

Composer validate: GREEN
Autoload integrity: YELLOW for current workspace (last green before API Surface/GraphQL naming refactor)
Production PSR-4 skips: YELLOW for current workspace (last green before API Surface/GraphQL naming refactor)
Runtime doctor: GREEN

Broken refs: YELLOW (20 raw missing refs; V2 classification refresh pending)
PHPStan: YELLOW for current workspace (rerun blocked by approval usage limit)
Tests: YELLOW for current workspace (rerun blocked by approval usage limit)
Component suite structure: GREEN
Superglobal audit: GREEN
Component completion: PROVEN
Muscle restoration: COMPLETE
```

## 2. V2 Enterprise Platform Engines (ACTIVE)

V1 recovery is formally CLOSED. We are now executing V2.

[x] Stage 12: Public API and Compatibility Governance
[x] Stage 13: Extension and Plugin Architecture
[x] Stage 14: Benchmark and Performance Budget Suite
[x] Stage 15: Observability Contract
[x] Stage 16: Security Threat Model
[x] Stage 17: Failure Simulation and Runtime Resilience
[x] Stage 18: Package Split Readiness
[x] Stage 19: Release, Upgrade and Migration Policy

**Engines to Build (ACTIVE DEVELOPMENT):**

- [ ] API Surface Engine - `components/API/Surface`, `components/API/OpenAPI`, and `components/API/GraphQL` are present;
  current Composer/PHPUnit/PHPStan validation is blocked by approval usage limit; REST, JSON:API, Webhooks, and RPC
  slices still pending
- [ ] Integration Engine - ObjectStorage exists only in `labs/Integration`; production `components/Integration` missing
- [ ] Reliability Engine - `components/Operations/Resilience` exists; V2 primitive completeness not proven
- [ ] Observability Engine - `components/Operations/Observability` exists; V2 trace/timeline/export/redaction acceptance
  not proven
- [ ] Runtime Supervision Engine - `components/Operations/RuntimeSupervision` exists; V2 completion not proven
- [ ] Memory Lifecycle Engine - `components/Operations/MemoryLifecycle` exists; V2 completion not proven
- [ ] Delivery Engine - `components/Operations/Delivery` exists; V2 completion not proven

## 3. V3 Executable System Design Framework (PLANNING CLOSED)

[x] Stage 20: System Design Kit
[x] Stage 21: Reference Architectures
[x] Stage 22: System Design Example Applications
[x] Stage 23: Final Documentation and Positioning
