# Remaining Risks

Task: Engineering Canon Convergence content depth and checker hardening.

## Risk Register

| Risk | Severity | Classification | Mitigation | Owner | Review Date |
|---|---|---|---|---|---|
| `baseline` and `full` modes are not implemented for the four new changed-scope checkers. | MEDIUM | YELLOW_TRACKED | Checkers return non-zero with exact RED message instead of fake GREEN. Changed mode is enforced now. | Governance tooling owner | 2026-06-26 |
| Domain story quality, pattern fit, cohesion, and some data correctness semantics remain manual review concerns. | MEDIUM | YELLOW_TRACKED | How-to docs now include rubrics, evidence templates, and manual review requirements. | Human reviewer + AI agent | 2026-06-26 |
| Plain `php` remains non-canonical in this local environment. | MEDIUM | YELLOW_TRACKED | Canonical command remains Composer or `/usr/bin/php8.4`; runners print runtime diagnostics and fail clearly when git is missing. | Local environment owner | 2026-06-26 |
| Two old top-level actual-changes archive artifacts remain untracked. | LOW | YELLOW_TRACKED | New packer excludes them and records them in skipped files. They were not copied into the fresh pack. | Human reviewer | 2026-06-02 |
| Full legacy repository validation, PHPStan legacy debt, Identity rewrite, and production framework/component readiness were not part of this pass. | HIGH | OUT_OF_SCOPE | Do not use this pass to claim production code GREEN. Use changed-scope governance/SDLC classification only. | Project owner | before Identity rewrite |

## Final Risk Classification

Changed-scope governance and SDLC hardening: GREEN.

Whole repository production readiness: not assessed in this pass.
