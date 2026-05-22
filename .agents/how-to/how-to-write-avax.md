# How To Write Avax

## Status

**MANDATORY** — This document defines Avax-specific governance deviations and
extensions on top of the reusable baseline profiles.

A rule without an explicit exception **MUST** be treated as mandatory.

Code review **MUST NOT** mark a scope GREEN when a mandatory rule is violated.

---

## 0. What This File Is

This file defines **Avax local deviations and extensions** from the reusable
governance baseline.

It does NOT duplicate reusable rules.
It does NOT weaken reusable rules.
It extends them where Avax has specific architectural discipline.

### Imports

This file imports and builds upon:

```
L1 Universal:  .agents/.rules/governance/standards/coding/how-to-coding-standards.md
L1 Universal:  .agents/.rules/governance/standards/coding/naming-standard.md
L1 Universal:  .agents/.rules/governance/architecture/architecture-standard.md
L2 Language:   .agents/.rules/governance/profiles/languages/php.md
L3 Framework:  .agents/.rules/governance/profiles/project-types/framework.md
```

When a rule is not mentioned here, the reusable baseline applies.

### Precedence

```
L4 Avax overlay (this file) wins for Avax-specific naming,
filesystem shape, component structure, stage lock, and local architecture rules.

L1-L3 reusable profiles provide the baseline for everything else.
```

---

## 1. Project Identity

### 1.1 Project Name

AvaX

### 1.2 Project Type

Runtime-agnostic PHP application platform and engineering system (Framework).

### 1.3 Primary Language

PHP 8.x (targeting PHP 8.5+).

### 1.4 Runtime

Multi-runtime: PHP-FPM, FrankenPHP, RoadRunner, Swoole, Workerman, ReactPHP, Amp, Fibers, CLI, tests, long-lived workers.

---

## 2. Local Architecture Rules

AvaX extends the reusable architecture baseline with the following local constraints.

### 2.1 Component Shape

Every production component **MUST** follow the canonical shape:

```text
components/<Area>/<Component>/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/
```

See `AGENTS.md §13` for the full canonical component shape.

### 2.2 Use Case to Flow Mapping (Cockburn)

AvaX maps Alistair Cockburn's use case goal levels to architecture layers:
- **Summary Level** → Component or Subsystem boundary.
- **User-Goal Level** → Flow Slice (`Flows/<ActionName>/`).
- **Subfunction Level** → Capability (`Capabilities/<CapabilityName>/`) or private helper method.

A subfunction **MUST NOT** become its own Flow. See `how-to-architecture.md §11.6` and `how-to-design-components.md §6.8.1`.

### 2.3 PEAA Pattern Translation (Fowler)

AvaX translates Fowler's PEAA patterns into screaming units:
- **Transaction Script** → Flow orchestrator class.
- **Domain Model (Rich)** → Domain Capabilities (Aggregates, Entities, Value Objects).
- **Table Data Gateway** → Persistence Capabilities (Repositories).
- **Service Layer** → PublicSurface facade or coordinating Flow.

Folders named after structural patterns (`TransactionScripts/`, `DomainModels/`, `Services/`) are **FORBIDDEN**. See `how-to-use-advanced-architecture-patterns.md §37`.

### 2.4 Balanced Coupling Control (Khononov)

Every unit **MUST** control coupling across three dimensions:
1. **Afferent/Efferent** — Capabilities must not depend on Flows or PublicSurface.
2. **Temporal** — Implicit sequencing via shared mutable state is forbidden.
3. **Semantic** — No shared internal schemas across sibling components.

See `how-to-architecture.md §58`, `how-to-design-components.md §32`, `how-to-code-review.md §27`.

### 2.5 Architecture Decision Records

Every significant architecture change **MUST** produce an ADR with trade-off matrix and fitness function gates. See `how-to-architecture-decisions.md`.

### 2.6 Domain Discovery Before Modeling

Tactical DDD elements **MUST NOT** be created without prior domain discovery (EventStorming or Domain Storytelling). See `how-to-architecture-extension-with-ddd.md §21.1`.

---

## 3. Local Naming Rules

Describe project-specific naming doctrine: forbidden names, concept word
translations, flow vs capability decisions, etc.

- Concept words that are not folder names
- Forbidden system folders
- Namespace ownership rules
- Test naming conventions

### 3.1 Facade DSL Naming

AvaX uses Laravel-inspired facades and fluent/DSL public APIs.
The Facade pattern is a legitimate framework dictionary term.

**Rules:**

- `Facade` is an allowed framework dictionary term and architectural role.
- Folder expresses the architectural role: `Facade/`
- Class expresses the public DSL noun: `Cache`, `Route`, `Config`, `Event`, `Log`, `App`
- Correct: `Facade/Cache.php` with `class Cache`
- Incorrect: `Facade/CacheFacade.php` — redundant, folder already says Facade
- Incorrect: `Gateways/CacheGateway.php` for local framework DSL — Gateway is for external boundaries only
- `Gateway` is reserved for external/integration/provider boundaries (StripeGateway, SmtpGateway, GitHubGateway)
- Hollow/fake facade behavior is forbidden — empty forwarding classes must be eliminated
- The Facade pattern itself is not forbidden — only hollow/fake instances

**Rationale:**

- Folder says architectural role (Facade)
- Class says public API word (Cache)
- Function says exact action (get, put, remember)
- `Cache::remember(...)` is the public DSL, not `CacheFacade::remember(...)` or `CacheGateway::remember(...)`
- `Facade/` already communicates the pattern; repeating `Facade` in the class name is redundant

---

## 4. Local Runtime/Execution Rules

### 4.1 Transaction Boundaries

Database transactions **MUST** be managed at the Flow layer or via explicit Unit of Work objects assembled at configuration time. Capabilities **MUST NOT** manage their own transactions. See `how-to-data-systems.md §2.2`.

### 4.2 Idempotency

Every data mutation flow initiated by external actors (HTTP, queues, webhooks) **MUST** use idempotency keys. See `how-to-data-systems.md §2.4`.

### 4.3 Cache Invalidation

Any flow mutating the System of Record **MUST** invalidate corresponding cached/derived states at or immediately after commit. Hard-coded TTL without proactive invalidation triggers is **FORBIDDEN**. See `how-to-data-systems.md §2.4` and `how-to-system-performance.md §13.3`.

### 4.4 Long-Lived Worker Safety

Singletons **MUST NOT** retain request-specific data. Static state must be bounded, immutable, resettable, or forbidden. See `how-to-system-performance.md §8.1`.

### 4.5 Runtime Hot Path Prohibitions

Runtime hot paths **MUST** avoid reflection, filesystem scans, config parsing, env reads, dynamic discovery, and object-graph assembly unless explicitly justified. See `AGENTS.md §1 Law 10`.

---

## 5. Local Evidence Rules

Describe what constitutes proof in this project: validation commands,
evidence locations, required artifacts.

- Canonical validation commands
- Evidence storage location
- Required artifacts per claim

---

## 6. Local Testing/Validation Rules

### 6.1 Flow Proof Tests

Every Flow Slice **MUST** have at least one sociable behavior test verifying end-to-end execution from PublicSurface entrypoint to repository adapter mock, asserting both success and failure paths. See `how-to-unit-test.md §92.1`.

### 6.2 Data Correctness Proof Tests

Repositories and data capabilities **MUST** have tests proving:
- Transaction rollback on partial failure.
- Idempotency protection (duplicate request returns cached result).
- Cache invalidation after SoR mutation.

See `how-to-unit-test.md §92.2`.

### 6.3 DDD Invariant Proof Tests

Aggregate roots **MUST** have solitary unit tests proving business invariants fail closed. No active database connections required for in-memory invariant proofs. See `how-to-unit-test.md §92.3`.

### 6.4 Gate Self-Test Rule

Every mandatory validation gate **MUST** have at least one negative test case. A gate that cannot fail is not a gate. See `how-to-production-readiness.md §14`.

### 6.5 Fitness Function Gates

Architecture fitness functions defined in ADRs **MUST** be implemented as automated tests or static analysis gates. See `how-to-architecture-decisions.md §3`.

---

## 7. Local Anti-Patterns

### 7.1 Forbidden Structural Patterns

- Folders named after PEAA structural patterns: `TransactionScripts/`, `DomainModels/`, `TableModules/`, `Services/`.
- Folders named after generic buckets: `Helpers/`, `Utils/`, `Common/`, `Shared/`, `Managers/`, `Core/`, `Support/`, `Adapters/`, `Contracts/`, `Handlers/`, `Processors/`.
- Subfunction-level goals promoted to their own Flow Slices.
- Flow orchestrator files exceeding 150 lines.

### 7.2 Forbidden Data Patterns

- Raw SQL mutations or direct Active Record updates outside Repository boundaries.
- Nested transactions / savepoints without explicit ADR approval.
- Cache entries for user/transactional data without invalidation triggers and namespaced keys.
- Writing to derived state before System of Record transaction commits.

### 7.3 Forbidden Coupling Patterns

- Circular dependencies between sibling components.
- Capabilities depending on Flows or PublicSurface.
- Implicit temporal coupling via shared mutable state or global side-effects.
- Semantic coupling via shared internal database schemas across component boundaries.

### 7.4 Forbidden DDD Anti-Patterns

- Creating tactical DDD elements (Aggregates, Entities, Repositories) without documented domain discovery (EventStorming / Domain Storytelling).
- Aggregates that cross transactional boundaries.
- Generic `Domain/Entities/ValueObjects/` folder hierarchies.

---

## 8. Local Exceptions

Document explicit exceptions to baseline rules. Each exception must include:
rule, path, reason, risk, owner, expiry, required cleanup, approval, validation.
Temporary exceptions without expiry are forbidden.

Default: None.

---

## 9. Cross-Reference Index

| Document | Covers |
|----------|--------|
| `how-to-architecture-decisions.md` | ADR template, trade-off matrix, fitness function gates |
| `how-to-architecture.md §58` | Balanced Coupling Rule (architecture level) |
| `how-to-architecture.md §11.6–11.8` | Use Case Goal Level Mapping, Flow size/complexity constraints |
| `how-to-design-components.md §6.8.1–6.8.3` | Use Case to Flow Translation Rule (component level) |
| `how-to-design-components.md §32` | Balanced Coupling Rule (component level) |
| `how-to-architecture-extension-with-ddd.md §21.1–21.3` | Domain Discovery, Scenario Input, DDD classifications |
| `how-to-data-systems.md` | Data system correctness, transactions, idempotency, cache invalidation |
| `how-to-use-advanced-architecture-patterns.md §37` | Fowler PEAA pattern translation |
| `how-to-code-review.md §27` | Balanced Coupling Review Rule |
| `how-to-code-review.md §28` | Trade-Off Analysis Review Rule |
| `how-to-code-review.md §29` | Data System Correctness Review Rule |
| `how-to-unit-test.md §92` | Enterprise Proof Rules (Flow, Data, DDD invariant) |
| `how-to-production-readiness.md §27.2` | Enterprise Data and Runtime Readiness Rule |
| `how-to-system-performance.md §13.3` | Cache Invalidation cross-reference to data systems |

When this file summarizes a rule, the referenced document is authoritative.

---

## 10. Override Semantics

This file declares Avax-specific overrides. The override behavior is:

1. **This file extends** reusable profiles — it does not replace them.
2. **When this file is silent**, the reusable baseline applies.
3. **When this file speaks**, it wins for Avax.
4. **Reusable profiles cannot be weakened** by this file for safety/security rules.
5. **Reusable profiles can be narrowed** by this file for stricter Avax discipline.
