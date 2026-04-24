# ARCHITECTURE NOTES

## Phase 0

- System Type: shared library / internal foundation component
- Primary Consumers: framework internals, HTTP/router support code, application services
- Runtime Context: mixed
- Lifecycle: stable core under structural cleanup
- Intended Use-Cases:
    - local file read/write/append/delete flows
    - directory lifecycle management
    - path existence, writability, and permission checks
    - configurable disk resolution with a local default
- Anti-Use-Cases:
    - cloud-object-storage SDK replacement
    - generic process/file watcher platform
    - dumping unrelated helpers into filesystem namespaces
- Non-Goals:
    - multi-driver expansion without a real consumer
    - reintroducing service/storage aliases for the same capability
    - broad public API growth without clear ownership
- Public API Stability Requirement: moderate
- Backwards Compatibility: required for the root facade, optional for removed internal legacy layers
- Performance Budget: standard application filesystem workloads

Primary axis:

> This system is fundamentally organized around **filesystem capabilities**.

Secondary axis:

> Secondary axis: **backend boundary via `Disks/*`**.

This is how the system actually works.

1. Callers enter through `Filesystem.php` or `FilesystemInterface.php`.
2. The root facade delegates behavior to one-purpose action owners under `Files/*` and `Directories/*`.
3. Path-specific checks live under `Paths/*`.
4. Runtime backend behavior is implemented by `Disks/Disk.php` and `Disks/Local/LocalDisk.php`.
5. `Configuration/*` owns assembly and default disk wiring.

# FINDINGS

### Finding: Legacy tests still targeted deleted service/storage layers

- Symptom: the active test tree still referenced `FilesystemService`, `LocalFileService`, `DirectoryInitializer`, and
  `Storage/*` classes that no longer exist in the live component.
- Root Cause: the source refactor outpaced the test tree cleanup.
- Impact: the test matrix described a false architecture and could not validate the real component shape.
- Evidence: replaced those characterization tests with current-shape coverage for the root facade, disk resolution, and
  directory writability.
- Risk Level: High

### Finding: Directory writability behavior was still hidden behind internal action owners

- Symptom: `EnsureDirectoryIsWritable.php` existed, but the root facade did not expose that capability.
- Root Cause: legacy service removal happened before the capability was folded into the public entry point.
- Impact: callers had to know internal classes to use a core directory lifecycle behavior.
- Evidence: `FilesystemInterface.php`, `AsyncFilesystemInterface.php`, and `Filesystem.php` now expose
  `ensureDirectoryIsWritable`.
- Risk Level: Medium

### Finding: Component docs were incomplete at the root level

- Symptom: `Foundation/Filesystem/docs/how-this-works.md` had frontmatter but no real explanation.
- Root Cause: folder mirroring was created before the explanatory pass was finished.
- Impact: ownership was harder to review and future refactors had less guidance.
- Evidence: completed component-local docs and added repo-level docs under `docs/Foundation/Filesystem`.
- Risk Level: Medium

### Finding: PHPUnit assertions in Filesystem tests were not executable as written

- Symptom: several tests used invalid named arguments such as `needdle`, `expected`, and `haystack` for PHPUnit
  assertion APIs.
- Root Cause: syntax-level validation was used, but runtime assertion signatures were not exercised in this workspace.
- Impact: tests would fail immediately once PHPUnit ran, hiding real behavioral regressions.
- Evidence: normalized remaining Filesystem tests to positional assertion calls and removed dead test files.
- Risk Level: High

# DECISION

Keep and Improve. The component axis is correct: one root facade, explicit capability folders, and a disk backend
boundary. The real problems were legacy test drift, incomplete public surfacing of directory writability, and unfinished
docs. Those are cleanup issues, not reasons to redesign the component.

# DECISIONS-LOG

- 2026-04-23: kept `Filesystem.php` as the single public runtime facade.
- 2026-04-23: exposed directory writability through the root contract instead of reviving removed services.
- 2026-04-23: retired dead service/storage characterization tests in favor of current-shape coverage.
- 2026-04-23: completed Filesystem review artifacts and documentation mirror.

# NEXT STEPS

- Run the Filesystem PHPUnit suite once PHPUnit is available in the environment.
- Expand `Disks/*` only when a real non-local backend consumer appears.
- Keep permission and writability behavior covered by regression tests because it is the easiest area to drift silently.
