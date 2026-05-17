# Governance Enforcement Map

This document maps rules to their source documents, checkers, and reports.

| Rule                                | Source Document                         | Checker                                                       | Report                                              |
|-------------------------------------|-----------------------------------------|---------------------------------------------------------------|-----------------------------------------------------|
| canonical component shape           | how-to-design-components.md section 6   | tooling/refactor/check-component-canonical-shape.php          | EVIDENCE/recovery-reports/component-shape-report.md |
| forbidden system folders            | how-to-design-components.md section 6.7 | tooling/refactor/check-forbidden-system-folders.php           | forbidden-system-folders-report.md                  |
| PublicSurface must not own behavior | how-to-design-components.md section 6.2 | tooling/refactor/check-public-surface.php                     | public-surface-report.md                            |
| no Adapters/ dumping ground         | how-to-design-components.md / GoF rule  | tooling/refactor/check-advanced-pattern-folder-violations.php | advanced-pattern-report.md                          |
| no Commands/ Queries/ Handlers/     | how-to-design-components.md / GoF rule  | tooling/refactor/check-advanced-pattern-folder-violations.php | advanced-pattern-report.md                          |
| no UseCases/ folder                 | how-to-design-components.md section 6.8 | tooling/refactor/check-forbidden-folders.php                  | forbidden-folders-report.md                         |
| no Security/Services/Managers/      | how-to-system-security.md section 38    | tooling/security/check-security-naming.php                    | security-naming-report.md                           |
| no Performance/Services/Managers/   | how-to-system-performance.md section 35 | tooling/performance/check-performance-naming.php              | performance-naming-report.md                        |
| docs mirror source                  | how-to-document.md                      | tooling/refactor/check-docs-mirror.php                        | docs-mirror-report.md                               |
| documentation location              | how-to-document.md / resolution rule    | manual review                                                 | N/A                                                 |
| filesystem-first documentation      | how-to-document.md section 3            | manual review                                                 | N/A                                                 |
| stage lock enforcement              | how-to-design-components.md section 5   | tooling/governance/check-stage-lock.php                       | stage-lock-report.md                                |
| governance index current            | GOVERNANCE_INDEX.md                     | tooling/governance/check-governance-index-current.php         | governance-index-report.md                          |
| component promotion                 | how-to-design-components.md section 4   | EVIDENCE/templates/component-promotion-checklist.md           | N/A                                                 |
| security baseline                   | how-to-system-security.md               | manual review                                                 | security-baseline.md                                |
| performance baseline                | how-to-system-performance.md            | manual review                                                 | performance-baseline.md                             |
| production readiness gates          | how-to-production-readiness.md          | manual review                                                 | production-readiness-report.md                      |

## Checker Types

### Automated (CI/CD)

- check-component-canonical-shape.php
- check-forbidden-system-folders.php
- check-public-surface.php
- check-advanced-pattern-folder-violations.php
- check-security-naming.php
- check-performance-naming.php
- check-stage-lock.php
- check-governance-index-current.php

### Manual Review Required

- docs mirror source
- documentation location
- filesystem-first documentation
- security baseline verification
- performance baseline verification
- production readiness gates
- component promotion checklist

## Report Locations

- Recovery reports: `EVIDENCE/recovery-reports/`
- Security reports: `EVIDENCE/security/`
- Performance reports: `EVIDENCE/performance/`
- Governance reports: `EVIDENCE/governance/`

## Validation Commands

| Check                     | Command                                                                                                    |
|---------------------------|------------------------------------------------------------------------------------------------------------|
| Component canonical shape | `php tooling/refactor/check-component-canonical-shape.php`                                                 |
| Forbidden folders         | `php tooling/refactor/check-forbidden-system-folders.php`                                                  |
| PublicSurface             | `php tooling/refactor/check-public-surface.php`                                                            |
| Advanced patterns         | `php tooling/refactor/check-advanced-pattern-folder-violations.php`                                        |
| Security governance       | `php tooling/security/check-security-naming.php`                                                           |
| Performance governance    | `php tooling/performance/check-performance-naming.php`                                                     |
| Stage lock                | `php tooling/governance/check-stage-lock.php`                                                              |
| Governance index          | `php tooling/governance/check-governance-index-current.php`                                                |
| Full test suite           | `vendor/bin/phpunit --no-coverage`                                                                         |
| Static analysis           | `vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress` |

---

This map is authoritative. Every rule must be traceable to a checker or manual review process.