# Architecture Overview — Feature-Sliced DDD with DSL-First




---

## 2) Guiding Principles

- DSL-First API: Developers express intent using a readable DSL; the engine handles translation to SQL.
- Separation of concerns: Queries, DSL builder, execution engine, and connections live in distinct, cohesive modules.
- Immutability where beneficial: Query state is immutable; builders produce new query instances.
- Single source of truth for SQL: SQL is generated inside the Engine layer; Actions modify the in-memory Query state, not the database directly.
- Enterprise-safety: Explicit guards, observability, and a consistent approach to errors and validation.
- Intuitive naming: Folder and file naming stays friendly and approachable, while preserving the architectural meaning.
- Note: This architecture is professional-grade. The term 'beginner-friendly' refers to the DSL surface and documentation clarity, not to the overall architectural complexity. The system remains a robust, enterprise-grade platform with a DSL you can learn quickly.\n- The DSL surface is beginner-friendly to learn quickly; the architecture remains professional-grade.\n

---

## 3) System Map (Modules and Roles)

- Database (core entrypoint)
  - Database.php, DatabaseManager.php, Facade.php
  - orchestrates modules and provides ergonomic access to the query API
- Connection
  - Driver implementations (MySQL, PgSQL, SQLite, Memory)
  - Connection lifecycle actions (Connect, Reconnect, Release, Pool)
  - Health checks (Alive, Health, Ping, Inspector)
  - Logging and auditing
  - Provider/Config (DatabaseConfig)
  - Examples and manifests
- Query (heart of the system)
  - Query.php (immutable data carrier)
  - Builder.php (DSL surface; how developers express queries)
  - QueryManager.php / Capabilities.php (optional orchestration for DSL and capabilities) – optional
  - Actions/From.php, Actions/Select.php, Actions/Where.php, ... (micro-actions that mutate the Query state)
  - Engine (QueryEngine) with sub-structure (Compiler, Dialect, Executor, Support)
  - Result
    - ResultSet (eager)
    - LazyResultSet (lazy streaming)
    - BatchResultSet (advanced; intended for advanced or high-volume use cases)
  - Tests and Examples (for devs and docs)
- Debugging, Schema, Migration, etc.
  - Debug, Schema, Migration modules exist as cross-cutting concerns

Notes:
- Names are intentionally intuitive for beginners, but the internal responsibilities map cleanly to the architecture concepts (Feature-Sliced + DDD + DSL-first).
- If you decide to rename folders later for even more beginner-friendly semantics, you can add tiny “readme” docs in each folder to map the concepts.

---

## 4) Key Concepts and Roles

- Query (State)
  - Plain data object describing what we want to do; no SQL inside.
  - Immutable by design to avoid side effects.
- Builder (DSL)
  - Fluent surface that developers actually code against.
  - Maps to the internal Actions that mutate the Query state.
- Actions (Micro-Actions)
  - Atomic intent: From, Select, Where, Execute, etc.
  - They modify the Query in predictable, testable ways.
  - Do not contain SQL or database specifics; they only describe the state transition.
- Engine (Execution)
  - The only place where SQL is emitted and executed.\n- DDMA: Internally, this resembles a Domain-Driven Micro-Action (DDMA) style, where each action represents a small, explicit domain intent.\n  - Contains Compiler, Dialect, and Executor responsibilities.
  - Ensures a single, consistent path from Query to SQL to bindings to results.
- Result (Read-Only Output)
  - Eager: ResultSet (immutable, in-memory)
  - Lazy: LazyResultSet (streaming, generator-based)
  - BatchResultSet (optional; batch processing over lazy results)
- Connection (Persistence Layer)
  - Driver implementations per database
  - Connection lifecycle, health checks, and logging
  - Dependency injection provider for easy swapping
- QueryManager (Optional Orchestration)
  - An optional glue/guard/feature-toggle layer that orchestrates DSL capabilities.
  - It is not mandatory; projects can replace it with a simple guard layer or integration glue if desired.
  - If used, it should not leak SQL knowledge into the higher layers.

---

## 5) File/Folder Naming Guidelines (Kids-Friendly, Yet Clear)

- Use names that communicate purpose, not just pattern. Examples:
  - Query.php (immutable data)
  - Builder.php (DSL surface)
  - QueryManager.php (optional orchestration)
  - Actions/From.php, Actions/Select.php, Actions/Where/*.php (micro-actions)
  - Engine/QueryEngine.php (the SQL factory + executor)
  - Result/ResultSet.php, Result/LazyResultSet.php
  - Connection/driver/MySQLConnection.php, Connection/driver/PgSQLConnection.php
  - Connection/check/Health.php (health checks)
- If you decide to lighten names for beginners, you can introduce alias-readmes that map to more technical terms (e.g., “Actions” → “What I want to do” in a tutorial). Keep the code names stable, but supplement with friendly docs.

---

## 6) Data Flow — How a Query Goes from DSL to Result

1) Developer writes DSL in Builder, e.g. From, Select, Where, etc.
2) Each DSL step invokes a micro-action that returns a new, enriched Query state.
3) The Query object, now fully built, is passed to the Engine.
4) Engine generates SQL via the Compiler/Dialect layer and resolves parameter bindings.
5) Engine executes SQL via PDO, returning either ResultSet (eager) or LazyResultSet (stream).
6) Higher-level features (like Batch Processing) can layer on top of LazyResultSet to process data chunk-by-chunk.

Note: The architecture deliberately keeps SQL and DB specifics inside Engine; DSL remains portable and purely descriptive.

---

## 7) Feature Expansion Rules (2-of-3 Rule)

When adding new features, ensure at least two of the following:

- Prevent production problems (safety, guards, validation)
- Introduce new capability without breaking existing API
- Make the system enterprise-safe (observability, error handling, validation)

If a potential feature doesn’t meet at least two, don’t add it.

Examples:
- Batch Processing API (fits triple: new capability with safe processing, supports existing API, enterprise-ready)
- Execution Guards (safety rails)
- Fingerprint + Observability (production monitoring groundwork)
- Read-Only Transaction Scope (safety for mixed workloads)
- Explain as First-Class Citizen (developer education + traceability)

---

## 8) Testing Strategy

- Unit tests focus on state transitions and behavior of Actions: ensure state transitions are correct and immutability is preserved.
- Integration tests for Query Engine: ensure Query -> SQL -> bindings -> ResultSet/LazyResultSet path works with a real (or in-memory) database.
- End-to-end tests for DSL flows: basic queries, complex where clauses, batch processing.
- Observability tests: ensure fingerprint + logs emit expected fields and metadata.
- Use mock drivers/DBs to keep tests fast and deterministic.

- SQL validation belongs to integration tests, not unit tests. Unit tests should verify state and transitions, not exact SQL strings.

Folder convention:
- tests/FeatureName/ (e.g., tests/BatchProcessing/)
- tests under Query: Actions, Engine, Result, etc.

---

## 9) Documentation Strategy

- Inline PHPDoc for every public API (Query, Builder, Engine, Actions, Result) with examples.
- A compact ARCHITECTURE.md (this file) to explain the large picture and the mental model.
- Per-feature READMEs in each folder (for beginners) that map to the DSL concepts.
- An Examples/README that shows practical, copy-paste DSL usage.
- A section in docs that mentions the 2-of-3 rule and governance.

---

## 10) How to Add a New Feature (Safe Handoff)

1) Add a new micro-action under Actions (e.g., Where/JsonContains.php) without touching Engine.
2) Extend Query with minimal exposure (immutability preserved) to accommodate the new action.
3) Update Engine only to handle the SQL path for the new feature (Compiler/Dialect), no changes to DSL surface.
4) Add tests and docs explaining the new capability and its usage.
5) Update the architecture docs with a short note on the feature and any new constraints.

---

## 11) Key Takeaways

- The architecture is intentionally modular and scalable: you can grow features horizontally without destabilizing core.
- DSL-first means the code reads like a sentence describing what you want; the engine handles the “how.”
- Safety and observability are baked in with explicit guards, fingerprinting, and logging.
- New features are introduced via micro-actions and engine augmentation, never by mutating core state or SQL paths in surprising ways.

---

If you want, I can:
- Create a concrete ARCHITECTURE.md file with this exact structure and copy-ready sections.
- Add a short glossary and mapping table that ties each folder to a beginner-friendly description.
- Propose a minimal table of contents for the docs directory to help maintain consistency as you grow.
