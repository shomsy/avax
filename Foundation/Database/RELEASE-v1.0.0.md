# Avax Database Component v1.0.0

## Release Notes

This release marks the **freeze** of the Database component core. All critical OWASP security concerns have been addressed, the architecture is stable, and the API surface is finalized.

### ✅ Security Hardening (OWASP Compliance)

- **Binding Redaction**: Query telemetry automatically masks sensitive parameters by default
- **Raw SQL Guardrails**: `raw()` and `selectRaw()` now enforce allowlist filters to block statement terminators, comments, and control characters
- **Safe Exception Handling**: `QueryException` exposes redacted bindings by default; raw access requires explicit opt-in

### ✅ Infrastructure Maturity

- **Connection Pool**: RAII-safe lifecycle with automatic slot release via `__destruct()`
- **Transaction Management**: Nested transactions with automatic rollback on failure
- **Identity Map**: Deferred execution for optimized batch commits
- **Execution Scope**: Correlation tracking for distributed tracing

### ✅ Observability

- **Event System**: `QueryExecuted`, `ConnectionAcquired`, `ConnectionFailed` signals with built-in redaction
- **Logging Integration**: PSR-3 compatibility via `DatabaseLoggerSubscriber`
- **Telemetry Control**: Environment toggle `DB_LOG_BINDINGS=raw` for explicit raw binding inspection

### 📚 Documentation

- [`README.md`](README.md) — Quick start guide
- [`ARCHITECTURE.md`](ARCHITECTURE.md) — Component overview
- [`SECURITY.md`](SECURITY.md) — Security guardrails and policies
- [`FREEZE.md`](FREEZE.md) — Core stability protocol

### 🧪 Test Coverage

- **Unit Tests**: `tests/Unit/CriticalPathTest.php` (transaction rollback, identity map, security redaction)
- **Stress Tests**: `tests/Stress/PoolStressTest.php` (RAII correctness under rapid acquire/release)

### 🔒 API Freeze

As of v1.0.0, the following modules are **frozen**:

- `Foundation/Connection/Pool/`
- `Foundation/Events/`
- `QueryBuilder/Core/Builder/QueryBuilder.php`
- `QueryBuilder/Core/Executor/QueryOrchestrator.php`
- `QueryBuilder/Core/Grammar/`
- `Foundation/Query/QueryState.php`

Changes to frozen modules require:

1. Documented security vulnerability, production incident, or breaking dependency change
2. Written justification and code review
3. Regression test coverage

### 🚀 Next Steps

- Build extensions as separate modules (not core modifications)
- Add integration tests for production scenarios
- Expand examples and usage documentation

---

**Stability over perfection. Discipline over features.**
