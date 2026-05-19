# Pass 15 Baseline Validation

Date: 2026-05-14
Branch: main
Commit: e0b8d184e

## Core Validation

| Command | Result |
|---------|--------|
| `composer validate --no-check-publish` | GREEN |
| `composer dump-autoload -o` | GREEN, 9274 classes |
| `vendor/bin/phpunit --no-coverage` | GREEN, 8289 tests, 23805 assertions |
| `vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G` | GREEN, 0 errors |

## Existing Gate Results

| Gate | Result |
|------|--------|
| Security blockers | GREEN |
| Component adoption | GREEN |
| Canonical shape | GREEN |
| Namespace drift | GREEN |
| Public surface | GREEN |
| Runtime leaks | GREEN |
| Advanced pattern folders | GREEN |
| Component suite structure | GREEN |
| Duplicate owners | GREEN |
| Raw file operations | GREEN_WITH_WARNINGS (16 NEEDS_DESIGN_DECISION, 0 MUST FIX) |
| FailureBoundary attributes | GREEN |
| FailureBoundary dogfooding | GREEN |
| FailureBoundary local try-catch | GREEN |
| Events gates (10/10) | GREEN |
| Database gates (8/8) | GREEN |
| Component status lock gate | GREEN (30 components) |
| Health/doctor policy gate | **RED** — 8 missing health checks |
| Broken-reference audit | **RED_BY_CONTENT** — 19 missing symbols (see triage below) |

## Broken-Reference Audit Content Analysis

19 missing symbols, all classified:

| Ref | Severity | Scope | Classification |
|---|---|---|---|
| Avax\Components\Auth\Interface\HTTP\Middleware\AuthenticationMiddleware | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\Components\HTTP\Middleware\CorsMiddleware | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\Components\HTTP\Middleware\ExceptionHandlerMiddleware | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\Components\HTTP\Middleware\JsonResponseMiddleware | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\Components\HTTP\Middleware\SecurityHeadersMiddleware | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\Config\Architecture\DDD\AppPath | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\Facade\Facades\Route | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\HTTP\Response\Response | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort | CRITICAL | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Avax\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Memcached | CRITICAL | main tree | OPTIONAL_PHP_EXTENSION (phpstan-ignored) |
| NonExistentResourceType | MINOR | main tree | ACTIVE_CODE_BUG — must investigate |
| PhpCsFixer\Config | CRITICAL | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| PhpCsFixer\Finder | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Presentation\HTTP\Middleware\OfficeIpRestrictionMiddleware | MINOR | worktree only | EVIDENCE_HISTORICAL (worktree copy) |
| Redis | CRITICAL | main tree | OPTIONAL_PHP_EXTENSION (phpstan-ignored) |
| Spiral\RoadRunner\Http\PSR7Worker | MINOR | main tree | OPTIONAL_VENDOR (roadrunner adapter) |
| Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample | CRITICAL | main tree | OPTIONAL_VENDOR (rector tooling dev) |
| Symplify\RuleDocGenerator\ValueObject\RuleDefinition | CRITICAL | main tree | OPTIONAL_VENDOR (rector tooling dev) |

**Active code broken refs in main tree:** NonExistentResourceType only — must investigate and fix.
**All other main tree refs:** Optional extensions/vendors — acceptable with phpstan-ignore.
**All worktree refs:** Not in active autoload scope — scope gate to exclude `.qoder/worktrees/**`.

## Missing Planned Gates

| Gate | Status |
|------|--------|
| `tooling/runtime/check-callable-resolution.php` | NOT_FOUND |
| `tooling/governance/check-truth-consistency.php` | NOT_FOUND |
| `tooling/refactor/check-empty-production-classes.php` | NOT_FOUND |
| `tooling/refactor/check-broken-reference-semantics.php` | NOT_FOUND |
| `tooling/testing/check-nonzero-target-assertions.php` | NOT_FOUND |
| `tooling/components/check-health-proof-map.php` | NOT_FOUND |
| `tooling/components/check-component-status-lock-coverage.php` | NOT_FOUND |

## Health/Doctor Gate Status

8 of 12 runtime-critical components missing health checks:
- Application/Cache: missing from status lock entirely
- DataStack/Database: no health check
- HTTP/Router: no health check
- Operations/Events: no health check
- Operations/Logging: no health check
- Security/Redaction: no health check
- Security/Cryptography: no health check
- Framework/FailureBoundary: no health check

4 components have health checks (Application/Container, Application/Filesystem, Operations/Queue, Integration/ObjectStorage via status lock check).

## Next Action

Implement health checks, missing gates, fix NonExistentResourceType bug, scope broken-ref audit to main tree, complete status lock.
