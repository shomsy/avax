# Production Readiness

**Layer**: 3 (Framework)  
**Status**: MANDATORY  
**Applies to**: All framework-type projects

---

## 0. Purpose

This document defines the non-negotiable production readiness rules for any framework project governed by this harness.

It is not a vision document.
It is not a marketing document.
It is not a project-specific TODO list.

It answers one practical question:

```text
What defines production-readiness, and how is it proven?
```

A framework is production-ready only when:

```text
architecture, taxonomy, autoload, namespaces, tests, static analysis,
runtime safety, public API stability, security baseline, performance baseline,
observability, and documentation all agree.
```

---

## 1. Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, **BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless a documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

A rule without an explicit exception **MUST** be treated as mandatory.

---

## 2. Readiness Color Definitions

### GREEN

A section or component MUST be marked GREEN only when current validation evidence proves all requirements are met.

- [ ] expected files exist in canonical taxonomy
- [ ] code is in the correct owner (no technical dumping grounds)
- [ ] no duplicate owner remains
- [ ] namespaces are canonical and compliant with the project's autoloading standard
- [ ] autoload passes without warnings or skipped classes
- [ ] all relevant architecture and integrity checkers pass
- [ ] all tests pass (0 failures)
- [ ] static analysis is green at the configured level or honestly baselined
- [ ] documentation matches source reality
- [ ] remaining risks are documented and accepted

**Evidence Rule**: A GREEN claim without a timestamped validation report or a pointer to specific test/checker output is **UNPROVEN** and MUST be rejected.

**Severity:** BLOCKER

### YELLOW

A section may be marked YELLOW when:

```text
[ ] main goal is achieved
[ ] non-critical risks remain
[ ] risks are documented
[ ] next step is clear
```

YELLOW is an honest intermediate state. It is not failure. It is not green.

### RED

A section or component MUST be marked RED when any mandatory rule is violated or any of the following exist:

- [ ] autoload fails or has production warnings
- [ ] architecture/taxonomy checker fails
- [ ] namespace drift exists
- [ ] tests cannot load or fail to target current code
- [ ] public surface leaks internals or has hollow behavior
- [ ] runtime safety is unproven or has confirmed leaks
- [ ] source and documentation disagree
- [ ] static analysis has unbaselined errors

**Severity:** BLOCKER

---

## 3. Global Acceptance Criteria

Production readiness requires all of these to be green.

```text
[ ] Current truth source is current and trusted.
[ ] Project tree is frozen and documented.
[ ] Component taxonomy integrity is green.
[ ] Security is a suite, not a component.
[ ] No extra top-level production components exist outside final suites.
[ ] No nested system folders exist inside capabilities, foundation, or public surface.
[ ] Autoload dump passes with no skipped production classes.
[ ] Autoload dump passes with no skipped test classes.
[ ] All canonical tests are green.
[ ] Test runner loads the full suite.
[ ] Framework static analysis is green.
[ ] Component static analysis is green for every component.
[ ] Secondary static analysis (if configured) is green or has a conscious baseline that does not hide missing classes.
[ ] Broken reference audit has no unresolved internal references.
[ ] Documentation checks are green.
[ ] Documentation mirrors source.
[ ] Forbidden folder and naming checks are green.
[ ] Superglobal boundary audit is green.
[ ] Public surface checker is green.
[ ] Runtime leak checker is green.
[ ] Runtime composition leak checker is green.
[ ] DI container service locator checker is green.
[ ] DI direct instantiation checker is green.
[ ] DI service provider coverage checker is green.
[ ] Vendor monolith isolation checker is green.
[ ] Code style dry-run is green for the committed scope.
[ ] Management TODO, BUGS, and ACTIVE files are synchronized.
[ ] Every changed component has relevant tests or a documented reason.
[ ] Golden path application runs using public APIs only.
[ ] Runtime worker safety is proven.
[ ] Security baseline is documented and tested.
[ ] Performance baseline is documented and benchmarked.
[ ] Observability contract is documented and implemented at minimum level.
[ ] Compatibility bridges are documented, tested, and removable.
[ ] No forbidden patterns in runtime folders (Flows, Capabilities runtime, PublicSurface runtime).
[ ] No service locator pattern in runtime execution paths.
[ ] No class_exists() gating in runtime code.
[ ] No new Build* in runtime code.
[ ] No ?? new fallback in runtime code.
[ ] Gate enforcement tools are path/context-aware, not class-name allowlists.
```

---

## 4. Governance Exception Register Protocol

### 4.1 Definition of an Exception

A governance exception is a conscious, documented decision to temporarily or permanently waive a mandatory rule.

### 4.2 Mandatory Registration

Every governance deviation MUST be recorded in the **Exception Register**.
The register is a centralized ledger at a project-defined evidence path (e.g., `EVIDENCE/accepted-exceptions-ledger.md`).

### 4.3 Required Exception Data

An exception MUST include:

1. **Rule**: The exact rule being waived.
2. **Context**: Path, class, or component affected.
3. **Reason**: Why the rule cannot be followed.
4. **Risk**: What is the impact of waiving this rule?
5. **Mitigation**: How is the risk managed?
6. **Owner**: The person/agent who authorized the exception.
7. **Expiry**: When will the exception be reviewed or cleaned up?

### 4.4 Approval

Exceptions MUST be explicitly accepted by a human reviewer or a higher-tier governance authority.

**Silent exceptions are FORBIDDEN.**

A "workaround" that is not in the register is a governance violation.

**Severity:** BLOCKER

---

## 5. Component Readiness Matrix

Use this matrix as the canonical production-readiness view. Populate with project-specific components.

| Suite | Component | Taxonomy | Namespace | Tests | Static Analysis | Docs | Runtime-safe | Status |
|-------|-----------|---------:|----------:|------:|----------------:|-----:|-------------:|--------|
| ...   | ...       |     TBD |       TBD |   TBD |             TBD |  TBD |          TBD | RED    |

Legend:

```text
TBD     = not audited in this report version
PARTIAL = some targeted tests or implementation exists
YELLOW  = promising but not fully proven
RED     = not production-ready
GREEN   = proven and validated
```

The matrix MUST be kept current. A component with no status MUST NOT be silently treated as GREEN.

---

## 6. Agent Execution Contract

Every agent working on production readiness must report:

```text
1. Stage name.
2. Scope.
3. Files changed.
4. Files intentionally not touched.
5. Validation commands run.
6. Command output summary.
7. Remaining risks.
8. Final GREEN/YELLOW/RED status.
```

No raw conversational summary counts as completion.

If command execution is blocked by environment limits:

```text
[ ] Record the exact command.
[ ] Record when it was blocked.
[ ] Record what was changed before the block.
[ ] Do not mark validation as complete.
```

---

## 7. Security Must Scream Rule

**Status:** MANDATORY  
**Severity:** BLOCKER

### Rule

Security-sensitive findings MUST be loud, explicit, and blocking by default.

Any OWASP-class weakness, injection risk, authentication bypass, authorization bypass, sensitive data leak, unsafe deserialization, unsafe redirect, filesystem traversal, command execution risk, SSRF risk, XSS risk, CSRF risk, SQL/query injection risk, weak cryptography, secret exposure, unsafe logging, or session/cookie weakness MUST be classified as HIGH or BLOCKER unless proven otherwise.

Security findings MUST NOT be hidden as:

```text
cleanup
style issue
minor refactor note
pre-existing harmless debt
accepted risk without owner/expiry
non-blocking note
code quality nit
low-priority cleanup
```

A security finding may be downgraded only with:

```text
exact threat explanation
affected path
exploitability assessment
mitigation proof
test or gate evidence
owner
expiry if accepted temporarily
truth/backlog entry
explicit stage-blocking decision
```

### Minimum Severity Rule

- exploitable or likely exploitable security weakness = **BLOCKER**
- potential OWASP-class weakness = **HIGH** or **BLOCKER**
- defense-in-depth gap = **MEDIUM** or **HIGH**, depending on blast radius
- documentation-only security clarification = **LOW** only when no exploit path exists

---

## 8. Security Review Trigger Rule

**Status:** MANDATORY  
**Severity:** HIGH

### Rule

Security review is mandatory when a change touches any of the following:

```text
authentication
authorization
roles/permissions
sessions
cookies
CSRF
CORS
redirects
user input
request parsing
validation
serialization/deserialization
database query building
filesystem I/O
file upload/download
logging
secrets
hashing
encryption
HTTP client/server
queues and message payloads
cache keys containing user or user-derived data
template/rendering
command/process execution
event payloads crossing boundaries
webhooks
signed URLs
tokens
API keys
password reset flows
rate limiting
tenant isolation
sandboxing
plugin execution
object storage paths
URL generation
proxy/trusted header handling
```

### Required Review Evidence

If triggered, the review MUST include a compliance table:

| Area | Changed? | Risk checked | Finding | Severity | Fix/mitigation | Blocks commit? |
|------|---------:|-------------:|---------|---------:|---------------:|---------------:|

### Silent Skip Rule

If the change does not trigger security review, the governance review MUST explicitly state why.

Security review cannot be skipped silently.

---

## 9. Security Commit Block Rule

**Status:** MANDATORY  
**Severity:** BLOCKER

### Rule

A commit is FORBIDDEN if the current change introduces, exposes, or leaves unresolved any security issue classified as BLOCKER, HIGH, OWASP-class weakness, authentication bypass, authorization bypass, injection risk, XSS risk, CSRF risk, SSRF risk, unsafe redirect, unsafe deserialization, path traversal, command execution risk, secret exposure, sensitive data logging, weak cryptography/hashing, session/cookie weakness, unsafe file upload/download, database query injection risk, unsafe event payload crossing trust boundary, unsafe queue payload handling, unsafe tenant boundary, or unsafe plugin/sandbox execution.

### Required Action

1. Do not commit.
2. Document the finding.
3. Fix it first.
4. Rerun validation.
5. Rerun security review.
6. Rerun recursive governance review.
7. Commit only when security review is clean.

### Temporary Acceptance

A security issue may remain only if final status is YELLOW or RED. Never GREEN.

If temporarily accepted, it MUST have:

```text
exact issue
affected path
severity
exploitability assessment
owner
mitigation
expiry or version
backlog or truth entry
evidence
explicit decision whether it blocks the current stage
```

### GREEN Commit Rule

A GREEN commit with unresolved security issue is forbidden.

---

## 10. Gate Self-Test Rule

**Status:** MANDATORY  
**Severity:** BLOCKER

### Rule

Every mandatory validation gate or architecture test MUST have at least one negative test case (proving it fails when the rule is violated).

- A gate that cannot fail is not a gate.
- A gate with "0 scans" or "0 violations" is UNPROVEN until the scanner's ability to find violations is verified.
- Mandatory gate without self-test or proof is BLOCKER for production-ready GREEN status.

Required negative test categories:

```text
Runtime composition gate must fail on:
  hidden fallback dependency construction
  direct instantiation of runtime services in business code
  runtime wiring via class_exists()

DI gate must fail on:
  hidden fallback construction
  container service locator in runtime code

Git/ignore gate must fail on:
  committed cache/test artifacts
  forbidden local tool files

Security gate must fail on:
  obvious injection pattern
  unsafe redirect pattern
  sensitive logging pattern
  path traversal pattern
```

Rules:

- gate without self-test or proof is YELLOW at minimum
- mandatory gate without self-test or proof cannot close BLOCKER
- gate that scans zero active files is FAIL, not PASS
- gate PASS with RED content is FAIL

---

## 11. No Zero-Scan Gate Rule

**Status:** MANDATORY  
**Severity:** BLOCKER

### Rule

A mandatory gate that scans zero active files is failed, not passed.

A gate result MUST report:

```text
scanned files count
excluded files count
exclusion reasons
active findings count
accepted exceptions count
false positives count
exit code
final classification
```

If a gate cannot determine its scan scope, it is unreliable and cannot support GREEN status.

```text
NOT_FOUND is not PASS.
UNAVAILABLE is not PASS.
SKIPPED is not PASS unless explicitly allowed by stage scope.
Exit 0 with RED content is not PASS.
```

---

## 12. Quality Ratchet Rule

**Status:** MANDATORY  
**Severity:** BLOCKER

### Rule

When a quality metric improves, the new better baseline becomes the floor.

Future passes MUST NOT regress below the last proven baseline unless explicitly accepted as YELLOW or RED with owner, expiry, risk, and recovery plan.

Tracked metrics include:

```text
static analysis error count
test runner errors/failures
mutation score if available
runtime composition findings
runtime assembly findings
public surface violations
status lock coverage
health/doctor check coverage
security findings
performance benchmark baseline
weak test count
ignored/suppressed static-analysis count
gate self-test coverage
component health-check coverage
```

Important rule:

- If a metric is established at 0, it MUST stay at 0.
- If a metric is not yet 0, the accepted current baseline becomes the temporary floor until improved.

Remaining nonzero debt MUST be classified with:

```text
exact count
owner
target stage/version
risk
blocking decision
evidence
```

Quality ratchet evidence must include:

| Metric | Previous baseline | New baseline | Command | Evidence file | Owner | Blocks next stage? |
|--------|------------------:|-------------:|---------|---------------|-------|-------------------:|

---

## 13. Status State Machine Rule

**Status:** MANDATORY  
**Severity:** BLOCKER

### Rule

Status labels must be exact. The following are the ONLY valid statuses:

### GREEN

All of the following MUST be true:

```text
all mandatory validation for the scope is clean
mandatory gates pass cleanly
governance review is clean
evidence exists
truth matches validation
no unclassified active blockers remain
no unresolved HIGH/BLOCKER security issue exists
no mandatory gate has RED content
no fake GREEN claim exists
```

### YELLOW

All of the following MUST be true:

```text
known exact blockers or debt remain
every remaining issue has owner, target, risk, and blocking decision
validation may be partially clean
truth is honest
work may be accepted only as evidence or partial closure
unresolved security issue may remain only with explicit owner, mitigation, expiry, and truth/backlog entry
```

### RED

Any of the following is true:

```text
validation is broken
truth is broken
mandatory gates are unreliable
active runtime/security/data-loss blockers remain
status cannot support next-stage work
commit/push is forbidden unless explicitly committing evidence-only RED/YELLOW closure is approved by workflow
```

### Allowed Final Statuses

- **FULL_GREEN**: validation clean, gates clean, governance review has zero unresolved findings.
- **GREEN_WITH_ACCEPTED_YELLOW_DEBT**: validation/gates clean, no BLOCKER/HIGH/MEDIUM remains, but YELLOW findings are formally accepted.
- **YELLOW_WITH_EXACT_BLOCKERS**: findings remain and may affect readiness.
- **RED**: validation, security, truth, or mandatory gates are broken.

### Forbidden Patterns

```text
"pre-existing" is not a status — every pre-existing issue must be classified as GREEN/YELLOW/RED
"Remaining YELLOW: None" when static analysis or gates have findings — forbidden
"PASS" with RED content — forbidden
"GREEN" with missing mandatory validation — forbidden
"pre-existing" without owner/target/risk — forbidden
"security note" without severity — forbidden
"non-blocking security issue" without mitigation/expiry/evidence — forbidden
```

---

## 14. Final Verdict

The final verdict for any production readiness assessment MUST be one of:

```text
GREEN
YELLOW
RED
BLOCKED
PARTIAL
UNKNOWN
```

The verdict MUST be supported by current validation evidence.

A verdict without evidence is an opinion, not a governance result.

If evidence is missing for any mandatory gate, the verdict MUST reflect that gap honestly.

---
