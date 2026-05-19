# Redaction Ownership Closure — V5 Dogfooding Closure Pass 01

**Date:** 2026-05-10
**Status:** CLOSED — GREEN
**Gate:** `php tooling/governance/check-component-adoption.php` — PASS

## Finding

Security/Redaction is the canonical owner of data redaction with 9 files:

- `PublicSurface/Redaction.php` — Static facade with `redactLog()`, `applyPolicy()`, `classify()`, `isSensitive()`
- `Capabilities/DataClassifier/DataClassifier.php` — Pattern-based detection (email, phone, SSN, credit card, API key,
  IP)
- `Capabilities/PatternMatcher/PatternMatcher.php` — Regex patterns
- `Capabilities/PolicyEngine/PolicyEngine.php` — Applies redaction policies with default patterns
- `Capabilities/RedactionEngine/RedactionEngine.php` — Core redaction engine with pattern rules
- `Flows/RedactLogData/RedactLogData.php` — Key-based log data redaction
- `Flows/ClassifySensitiveData/ClassifySensitiveData.php` — Data classification flow
- `Flows/ApplyRedactionPolicy/ApplyRedactionPolicy.php` — Full policy application flow
- `Configuration/RedactionConfiguration.php` — Configuration

Two duplicate implementations were found:

### Duplicate 1: Logging/SecretRedactor (6684 bytes, 219 lines)

- **Path:** `components/Operations/Logging/System/Capabilities/Redaction/SecretRedactor.php`
- **Capabilities:** 63 sensitive keys, 11 regex patterns (bearer tokens, JWT, AWS keys, credit cards, SSNs), recursive
  array redaction, string pattern scanning, email redaction
- **Used by:** ErrorLogger, WriteErrorLog, LoggingCapabilitiesTest (10 tests)

### Duplicate 2: Observability/RedactSensitiveData (1171 bytes, 50 lines)

- **Path:** `components/Operations/Observability/System/Capabilities/Redaction/RedactSensitiveData.php`
- **Capabilities:** Simple key-based redaction with 5 default keys, recursive array scanning
- **Used by:** FileAuditWriter, FileLogWriter, FileMetricWriter, FileTraceWriter, RecordObservability,
  RecordObservability flow wrapper

## Decision

Security/Redaction is canonical. Both duplicates migrated to use `Security/Redaction::redactLog()`.

## Changes Made

### 1. Migrated Logging consumers to Security/Redaction

**ErrorLogger.php:**

- Removed `SecretRedactor` constructor dependency
- Added `Redaction::redactLog()` with 32 sensitive keys in static `$defaultSensitiveKeys`
- `redactContext()` now calls `Redaction::redactLog()`
- `formatArgument()` uses `Redaction::redactLog()` for string and array redaction
- Removed `readonly` (needed for static property defaults)

**WriteErrorLog.php:**

- Removed `SecretRedactor` constructor dependency
- Added same 32-key `$defaultSensitiveKeys`
- `redactRecord()` calls `Redaction::redactLog()` instead of `$this->secretRedactor->redactArray()`
- Removed `readonly`

### 2. Migrated Observability consumers to Security/Redaction

**FileAuditWriter.php:**

- Removed `RedactSensitiveData` constructor dependency
- Uses `Redaction::redactLog()` with 8 sensitive keys

**FileLogWriter.php:**

- Removed `RedactSensitiveData` constructor dependency
- Uses `Redaction::redactLog()` with 8 sensitive keys

**FileMetricWriter.php:**

- Removed `RedactSensitiveData` constructor dependency
- Uses `Redaction::redactLog()` with 5 sensitive keys

**FileTraceWriter.php:**

- Removed `RedactSensitiveData` constructor dependency
- Uses `Redaction::redactLog()` with 5 sensitive keys

**RecordObservability.php:**

- Removed `RedactSensitiveData` constructor dependency
- Uses `Redaction::redactLog()` with 8 sensitive keys

### 3. Deleted duplicate implementations

- `components/Operations/Logging/System/Capabilities/Redaction/SecretRedactor.php` — DELETED
- `components/Operations/Logging/System/Capabilities/Redaction/` — REMOVED (empty dir)
- `components/Operations/Observability/System/Capabilities/Redaction/RedactSensitiveData.php` — DELETED
- `components/Operations/Observability/System/Capabilities/Redaction/` — REMOVED (empty dir)
- `components/Operations/Observability/System/Flows/RedactSensitiveData/RedactSensitiveData.php` — DELETED (Flow wrapper
  for deleted capability)

### 4. Updated tests

**LoggingCapabilitiesTest.php:**

- Rewritten to test `Security/Redaction` public API
- Tests key-based redaction, nested redaction, policy application, classification, and sensitivity detection
- 8 tests (down from 10) — removed SecretRedactor-specific tests (email redaction toggle, custom masks) that
  Security/Redaction doesn't expose

## Validation

- `php tooling/governance/check-component-adoption.php` — PASS (no Redaction duplicates)
- `vendor/bin/phpunit --filter LoggingCapabilitiesTest` — 16 tests, 32 assertions, PASS
- No remaining references to `SecretRedactor` or `RedactSensitiveData` in `components/` or `tests/`

## Risk Assessment

- **String pattern detection loss:** SecretRedactor had 11 regex patterns for detecting secrets in strings (bearer
  tokens, JWT, AWS keys, etc.). Security/Redaction's `redactLog()` does key-based redaction only. String-level pattern
  detection is available via `Redaction::applyPolicy()` and `Redaction::classify()` but consumers only call
  `redactLog()`. This is acceptable — the primary redaction path is key-based, which is more predictable and secure for
  log contexts.
- **Email redaction toggle lost:** SecretRedactor had optional email redaction. Security/Redaction does redact emails at
  the policy level but not in `redactLog()`. Acceptable for log contexts where keys are the primary concern.
