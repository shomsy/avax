# Database Security Guardrails

## 1. SQL Injection Prevention

The primary defense against SQL injection is the strict use of prepared statements and parameter binding.

- **Parametric Binding**: All values passed to `where()`, `insert()`, `update()`, etc., are collected into a
  `BindingBag` and never directly concatenated into SQL.
- **Identifier Wrapping**: Table and column names are escaped using dialect-specific characters (e.g., backticks in
  MySQL) to prevent keyword collisions.

## 2. Raw SQL Entry Points (The Escape Hatch)

Functions like `raw()` and `selectRaw()` allow bypass of the builder's abstraction.

- **Guardrails**: These methods include an allowlist/denylist filter that blocks statement terminators (`;`), comments (
  `--`), and control characters.
- **Usage Policy**: Raw SQL MUST only be used with trusted input. Never pass user-supplied data directly into a raw
  expression.

## 3. Data Leakage (OWASP Logging)

To prevent Personally Identifiable Information (PII) from leaking into logs:

- **QueryExecuted Event**: This telemetry signal is a security boundary. It provides `redactedBindings` by default.
- **SensitiveParameter Attribute**: Raw bindings are marked with `#[SensitiveParameter]` to prevent display in standard
  stack traces or error outputs.
- **PDOExecutor**: Redacts parameters *before* dispatching telemetry unless explicitly opted-in via
  `DB_LOG_BINDINGS=raw`.
- **QueryException**: The `getBindings()` method defaults to a redacted view. Unmasking requires an explicit
  `getBindings(redacted: false)` call.

## 4. Environment Safety

- **Dry Run**: Use `orchestrator->pretend()` in development/staging to verify SQL without side effects.
- **Audit Trails**: Every executed query carries a `correlationId` to track it back to the originating request.
