# Diagnostics Contracts

`CompileReport` and `RuntimeReport` are stable machine-readable contracts. They are not ad hoc debug dumps.

## Versioning

- `CompileReport::SCHEMA_VERSION`
- `RuntimeReport::SCHEMA_VERSION`

Each JSON payload emits `schemaVersion` at the top level.

## Stability Guarantee

Minor internal implementation changes may add fields, but existing documented fields are treated as stable within the same schema version.

Changing semantics or removing documented fields requires:

1. a schema version bump
2. docs update
3. CI diagnostics contract validation

## Compile Report Fields

Canonical top-level fields include:

- availability and compatibility
- freshness state
- warnings
- compile mode and environment
- checksum validity
- service and index counts
- invalidation reasons
- statistics
- typed metadata snapshot

## Runtime Report Fields

Canonical top-level fields include:

- registration and compiled revisions
- compiled attach and warmup state
- diagnostics mode and timeline state
- shared/scoped counts
- aliases and deferred providers
- metrics and timeline
- scope snapshot
- hot-path summary
- nested compile report

## CI Validation

The contract validator lives at:

- [`../tests/diagnostics/validate-report-schemas.php`](../tests/diagnostics/validate-report-schemas.php)
- [`../tests/check-diagnostics-contracts.sh`](../tests/check-diagnostics-contracts.sh)

CI should fail if required keys disappear, schema versions drift unexpectedly, or JSON output stops round-tripping.
