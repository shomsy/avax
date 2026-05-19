# 04 — Code Smell and Governance Scan

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** Governance smell scan across framework and components

## Scan Results

### `?? new` / `?: new` (inline fallback construction)

**Result:** No production hot-path violations found. ALLOWED in composition root and configuration files.

### `new .*Factory` (runtime factory construction)

**Result:** Found in configuration and bootstrap files. ALLOWED_COMPOSITION_ROOT.

### Forbidden names (Manager/Service/Helper/Util/Support)

**Result:** No forbidden folder names found. Some class names contain these words but are in properly-named capability
directories. ALLOWED_OWNER.

### Reflection in runtime hot paths

**Result:** Reflection found in test files, compilation, and metadata pre-warm paths. ALLOWED_TEST,
ALLOWED_COMPILE_PATH. No hot-path reflection in runtime request flow.

### Raw file operations

**Result:** Found in:

- Filesystem/Storage capabilities themselves — ALLOWED_OWNER (these ARE the filesystem capability)
- RotatingFileWriter — ALLOWED_OWNER (logging writer owns file I/O)
- MigrationGenerator — ALLOWED_TOOLING (writes migration files)
- TemplateEngine — ALLOWED_BOOTSTRAP (configures Blade at boot)

### Superglobals ($_GET/$_POST/$_SERVER/$_COOKIE/$_FILES/php://input)

**Result:** Isolated behind RuntimeRequest/Request boundaries in framework/System. ALLOWED_BOOTSTRAP.

### error_log

**Result:** Used as fallback in FailureBoundary ReportFailure (structured, redacted). ALLOWED_FALLBACK. Not primary
production path.

### Broad catch blocks (catch Throwable/Exception)

**Result:** Found in:

- HTTP lifecycle boundary (HandleIncomingHttp) — ALLOWED — lifecycle safety net
- FailureBoundary pipeline — ALLOWED — local recovery boundary
- Worker/runtime outer boundaries — ALLOWED — resource cleanup
  All are lifecycle boundary, local recovery, or resource cleanup.

### sleep/usleep in tests

**Result:** Found in Timeout and concurrency tests. ALLOWED_TEST — guarded with reasonable limits.

## Conclusion

No HOT_PATH_VIOLATION, FLOW_SMELL, or RUNTIME_STATE_RISK found.
All findings classified as ALLOWED_OWNER, ALLOWED_BOOTSTRAP, ALLOWED_COMPOSITION_ROOT, ALLOWED_COMPILE_PATH,
ALLOWED_TOOLING, ALLOWED_TEST, or ALLOWED_FALLBACK.
