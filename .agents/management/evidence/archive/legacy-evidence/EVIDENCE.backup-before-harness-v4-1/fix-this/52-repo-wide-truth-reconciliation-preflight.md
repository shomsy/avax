# Repo-Wide Truth Reconciliation Preflight

**Date:** 2026-05-16

## 1. Branch and Commit

- **Branch:** main
- **Commit:** 370e20252 (hardening: reconcile phase b proof with code and evidence)
- **Status:** M .agents/how-to/how-to.txt, M avax.txt (unrelated pre-existing dirty files)

## 2. Current Phase B Status

- Phase B Proof Consistency Correction: COMPLETE / FULL_GREEN_PHASE_B_PROOF_CONSISTENCY_CLOSED_AND_V5_9_READY
- Evidence files 41-51 created
- All validation GREEN
- All gates PASS
- Recursive governance review: 0 unresolved findings

## 3. Current V5.9 Readiness Claim

- V5_9_READY
- All Phase A/B debts closed
- Provider wiring proven
- PHPDoc clean (including provider methods)
- Runtime gate fixture proof added

## 4. Exact Concern Being Reconciled

Independent review has seen older dumps/snapshots containing:

- `HTTP/ApiVersioning/System/PublicSurface/ApiVersion.php` (with lazy `new VersionRegistry()`)
- `HTTP/ApiVersioning/System/Capabilities/Resolution/VersionResolver.php` (with `VersionRegistry|null` fallback)
- Inline `ApiVersionResolved` duplicate
- Stale duplicate code outside `components/`

The Phase B consistency correction report said "CONFIRMED ABSENT" based on a single `find` command.
This pass must prove definitively from the **current real repository state** whether:

- A) Top-level `HTTP/ApiVersioning/` is truly absent
- B) It exists as tracked or untracked code
- C) It exists only in snapshot/dump/evidence files

## 5. Commands to Be Run

```bash
# Filesystem scans
pwd
git rev-parse --show-toplevel
test -d HTTP && find HTTP -maxdepth 5 -type f | sort
test -d HTTP/ApiVersioning && find HTTP/ApiVersioning -type f | sort
find . -path './HTTP/ApiVersioning/*' -type f | sort
find . -path './*/HTTP/ApiVersioning/*' -type f | sort
find . -type f -name '*.php' | grep -E '(^|/)HTTP/ApiVersioning/' | sort

# Git tracked file scans
git ls-files 'HTTP/ApiVersioning/**'
git ls-files '*HTTP/ApiVersioning*'
git ls-files | grep -E '(^|/)HTTP/ApiVersioning/'

# Pattern scans
grep -R "new VersionRegistry" --include='*.php' .
grep -R "VersionRegistry|null" --include='*.php' .
grep -R "ApiVersionResolved" --include='*.php' .
grep -R "\?\?= new VersionRegistry" --include='*.php' .
grep -R "\?\? new VersionRegistry" --include='*.php' .

# Autoload proof
composer dump-autoload -o
php -r "require 'vendor/autoload.php'; echo class_exists('Avax\\\\Components\\\\HTTP\\\\ApiVersioning\\\\System\\\\PublicSurface\\\\ApiVersion') ? 'yes' : 'no';"
```

## 6. Paths to Inspect

- `HTTP/ApiVersioning/` (top-level, if exists)
- `components/HTTP/ApiVersioning/` (canonical, confirmed active)
- `avax.txt` (may contain snapshot dump evidence)
- `Framework.txt` (may contain old snapshot evidence)
- `Components.txt` (may contain old snapshot evidence)
- Any backup/archive files containing `HTTP/ApiVersioning` paths

## 7. Expected Final Decisions

Most likely outcome: **ABSENT_IN_CURRENT_REPO**

- Top-level `HTTP/ApiVersioning/` does not exist in current repo
- Earlier concern came from old uploaded snapshots/dumps (avax.txt, Framework.txt, etc.)
- Current active ApiVersioning code lives only under `components/HTTP/ApiVersioning/`
- Evidence must explicitly state this distinction

If present: classify, remove, or fix as required by governance.

## 8. Validation Commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/components/check-component-runtime-assembly.php
php tooling/refactor/check-public-surface.php
php tooling/components/check-hollow-public-surfaces.php
```

## 9. Final Status Rules

- FULL_GREEN requires: top-level HTTP/ApiVersioning proven absent OR classified/removed
- If stale code exists and is removed → FULL_GREEN candidate after validation
- If stale code exists and is active → must fix before commit
- If report contradicts repo → RED until reconciled
