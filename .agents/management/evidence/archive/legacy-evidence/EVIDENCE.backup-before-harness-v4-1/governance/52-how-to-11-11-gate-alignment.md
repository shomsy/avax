# How-To 11/11 Gate Alignment

## Gate Alignment Table

| Rule                            | Canonical document                    | Gate/tool                                      | Exists?          | Self-test exists? | Gap                                         | Blocks V5.9? |
|---------------------------------|---------------------------------------|------------------------------------------------|------------------|-------------------|---------------------------------------------|--------------|
| Runtime composition leaks       | `how-to-runtime-composition.md`       | `check-runtime-composition-leaks.php`          | Yes              | Partially         | Self-test not confirmed in tooling          | No           |
| DI hidden fallback construction | `how-to-dependency-injection.md` §3.6 | `check-direct-instantiation.php`               | Yes              | Unknown           | —                                           | No           |
| Missing dependency boot failure | `how-to-dependency-injection.md` §3.7 | Container verify/boot                          | Design           | N/A               | Not gated                                   | No           |
| ServiceProvider coverage        | `how-to-dependency-injection.md` §4.0 | `check-service-provider-coverage.php`          | Yes              | Unknown           | —                                           | No           |
| PublicSurface behavior          | `how-to-design-components.md` §6.2    | `check-public-surface.php`                     | Yes              | Unknown           | —                                           | No           |
| Hollow PublicSurface            | `how-to-design-components.md` §6.2    | `check-hollow-public-surfaces.php`             | Appears to exist | Unknown           | —                                           | No           |
| Forbidden folders               | `how-to-design-components.md` §6.7    | `check-advanced-pattern-folder-violations.php` | Yes              | Yes               | —                                           | No           |
| Component status ownership      | `how-to-production-readiness.md` §21  | No dedicated gate                              | **MISSING**      | N/A               | Must be created or covered by manual review | **YELLOW**   |
| Truth consistency               | `AGENTS.md` §19                       | `check-truth-consistency.php`                  | Appears to exist | Unknown           | —                                           | No           |
| Recursive governance review     | `how-to-code-review.md`               | Manual review                                  | Manual           | N/A               | Not automatable                             | No           |
| Git forbidden files             | `how-to-git.md` §5                    | `.gitignore` + manual                          | Manual           | N/A               | —                                           | No           |
| PHPStan nonzero truth status    | `how-to-production-readiness.md` §20  | PHPStan                                        | Yes              | N/A               | —                                           | No           |
| Gate self-tests                 | `how-to-code-review.md` §14.2         | Manual review                                  | Manual           | N/A               | —                                           | No           |
| Zero-scan gate ban              | `how-to-code-review.md` §14.3         | Manual review                                  | Manual           | N/A               | —                                           | No           |
| Quality ratchet                 | `how-to-production-readiness.md` §18  | Manual review                                  | Manual           | N/A               | —                                           | No           |
| Exception register              | `how-to-production-readiness.md` §8   | Manual check                                   | Manual           | N/A               | —                                           | No           |
| Canonical terms                 | `docs/governance/canonical-terms.md`  | Manual review                                  | Manual           | N/A               | —                                           | No           |
| Security review triggers        | `how-to-system-security.md` §41       | Manual review                                  | Manual           | N/A               | —                                           | No           |
| Security commit block           | `how-to-git.md` §15                   | Manual review                                  | Manual           | N/A               | —                                           | No           |
| Critical quality signals        | `how-to-code-review.md` §24           | Manual review                                  | Manual           | N/A               | —                                           | No           |
| Large unit thresholds           | `how-to-code-review.md` §21           | Manual review                                  | Manual           | N/A               | —                                           | No           |

## Gaps

1. **Component Status Ownership gate** — no automated gate exists. Must be enforced via manual review during
   production-readiness checks. This is a YELLOW gap but does not block V5.9.
2. **Gate self-tests unproven** — existing gates may lack negative test cases. Must be verified in a focused pass.
3. **Security gate** — no dedicated automated security governance gate (e.g., scanning for forbidden patterns from
   Security Must Scream rule). Manual review only.

## Verdict

No gate gap blocks V5.9. All critical gates exist. The Component Status Ownership gate is the only missing gate, and it
can be enforced through manual review.
