# Human Decision Resolution — Pass 15

Date: 2026-05-14

## .qoder/worktrees/** Status

**Decision:** EXCLUDED_FROM_PRODUCTION_SCAN

**Reason:** `.qoder/worktrees/**` contains Qoder CLI agent workspace copies from May 7-8. These are:

- Generated local state, not source code
- Not part of active autoload path
- Not evidence or documentation
- Not cleanup targets

**Action:** The broken-reference semantics gate (`check-broken-reference-semantics.php`) now excludes
`.qoder/worktrees/**` paths from active code analysis. Worktree refs are classified as `EXCLUDED_PATH`.

**Risk:** Future agents copying worktree code into active paths would need fresh audit. Gate handles this.

**Owner:** AvaX maintainer
**Expiry:** Until worktree directories are cleaned or governance changes

## Optional Redis/RoadRunner/Tooling Dependency Policy

**Decision:** OPTIONAL_DEPENDENCY_NOT_REQUIRED_FOR_CURRENT_VALIDATION

| Reference                         | Type                                 | Classification                 |
|-----------------------------------|--------------------------------------|--------------------------------|
| Redis                             | Optional PHP extension               | Acceptable with phpstan-ignore |
| Memcached                         | Optional PHP extension               | Acceptable with phpstan-ignore |
| Spiral\RoadRunner\Http\PSR7Worker | Optional vendor (ROADMAP adapter)    | Acceptable                     |
| Symplify\RuleDocGenerator\*       | Optional dev vendor (rector tooling) | Acceptable                     |

**Policy:** These are optional runtime or tooling dependencies. They do not block V5.9 because:

- Boot DSL does not require Redis, Memcached, RoadRunner, or Symplify
- PHPStan already ignores these via phpstan.neon configuration
- Health/doctor gates provide warnings, not blocks, for optional dependency unavailability

**Owner:** AvaX maintainer
**Follow-up:** Document optional dependency policy in health/doctor warnings

## Health Invariant Ownership

**Decision:** Assigned per component in component status lock

Each active core health check now has an owner via the component status lock:

- Database health → DataStack/Database team (AvaX maintainer)
- Router health → HTTP/Router team (AvaX maintainer)
- Events health → Operations/Events team (AvaX maintainer)
- Cache health → Application/Cache team (AvaX maintainer)
- Logging health → Operations/Logging team (AvaX maintainer)
- Redaction health → Security/Redaction team (AvaX maintainer)
- Cryptography health → Security/Cryptography team (AvaX maintainer)
- FailureBoundary health → Framework team (AvaX maintainer)

No "unknown owner" remains for active core health checks.

## Missing Gate Implementation vs Formal Exception

**Decision:** Implement all mandatory planned gates

All 7 planned missing gates have been implemented:

1. `tooling/runtime/check-callable-resolution.php` — PASS
2. `tooling/governance/check-truth-consistency.php` — PASS
3. `tooling/refactor/check-empty-production-classes.php` — PASS
4. `tooling/refactor/check-broken-reference-semantics.php` — PASS
5. `tooling/testing/check-nonzero-target-assertions.php` — PASS
6. `tooling/components/check-health-proof-map.php` — PASS
7. `tooling/components/check-component-status-lock-coverage.php` — PASS

No formal exceptions needed. All gates implemented and passing.
