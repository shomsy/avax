# Large Unit Gate Classification

Date: 2026-05-16
Stage: V5.9 Governance Baseline Classification
Status: YELLOW_WITH_EXACT_AUTHBUILDER_BLOCKER

## 1. Gate Result

Command:

```bash
php tooling/governance/check-large-unit-thresholds.php
```

Raw output: `EVIDENCE/v5.9-codex/raw/large-unit-thresholds-after.txt`

| Metric           | Before | After |
|------------------|-------:|------:|
| Scanned files    |  14770 |  3906 |
| Excluded files   |  11333 | 22197 |
| BLOCKER findings |      1 |     1 |
| REVIEW findings  |    366 |   108 |
| Exit code        |      1 |     1 |

The count changed because the gate was corrected to exclude local dot-worktrees and IDE vendor paths (`.qoder/**`,
`.idea/**`, and other dot-prefixed paths). The real production blocker remains unchanged.

## 2. Required Table

| Unit                                                                     |                                       Lines | Gate severity | Real severity                                   |                                                                     Blocks V5.9? | Decision                                                                                                                                    | Next action                                                              |
|--------------------------------------------------------------------------|--------------------------------------------:|---------------|-------------------------------------------------|---------------------------------------------------------------------------------:|---------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------|
| `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` | 1730 by `wc -l`, 1731 by gate newline count | BLOCKER       | BLOCKER                                         | YES for pure GREEN; accepted as exact next blocker for this classification phase | Real oversized builder that owns configuration state, default dependency resolution, readiness validation, and final Auth runtime assembly. | Execute `EVIDENCE/v5.9-codex/07-authbuilder-split-plan.md` first slice.  |
| Large-unit REVIEW findings                                               |                                         108 | REVIEW        | REVIEW debt unless promoted by focused evidence |                                                                NO for this phase | Tracked as review debt; no runtime/security critical item was promoted during this classification pass.                                     | Address opportunistically or in focused phases after AuthBuilder.        |
| Local dot-worktree / IDE vendor findings                                 |              259 removed from current count | Scope noise   | Scope bug                                       |                                                                               NO | Gate now excludes dot-prefixed local paths.                                                                                                 | No further action unless another local/generated path pollutes the gate. |

## 3. AuthBuilder Inventory

| Item                     | Evidence                                                                                                                                |
|--------------------------|-----------------------------------------------------------------------------------------------------------------------------------------|
| Path                     | `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`                                                                |
| Line count               | `wc -l`: 1730                                                                                                                           |
| Public methods           | 46                                                                                                                                      |
| Private methods          | 1 (`capabilityRequests()`)                                                                                                              |
| Constructor dependencies | none; dependency state is stored through 40+ mutable builder properties                                                                 |
| Main assembly method     | `ready()` starts at line 706 and runs to the final return at line 1710                                                                  |
| Object graph assembly    | 100+ `new` expressions in `ready()`                                                                                                     |
| Test coverage found      | `tests/Unit/Components/Identity/Auth/AuthCapabilitiesTest.php`, `AuthFoundationSmokeTest.php`; no focused AuthBuilder graph tests found |
| Current risk             | Auth runtime graph assembly is too large to review safely, hides capability boundaries, and has weak direct test proof                  |

## 4. AuthBuilder Responsibility Classification

| Responsibility                                | Evidence                                                                                                                        | Classification                             |
|-----------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------|
| Core credential/password configuration        | `usingHasher()`, `usingIdGenerator()`, password reset store/throttle setters, registration/login/recovery assembly in `ready()` | Extractable first-slice candidate          |
| Session/current-authentication configuration  | session registry, current authentication, sessions, login/logout/change-password graph                                          | Separate owner                             |
| MFA configuration                             | MFA stores, TOTP, challenge store, backup/recovery/challenge/enrollment graph                                                   | Separate owner                             |
| Passkey configuration                         | passkey runtime, credential store, challenge store, relying-party options, passkey graph                                        | Separate owner                             |
| OAuth/OIDC/federation configuration           | OAuth registry, authorization code store, OIDC provider/store, federation stores/runtime, external identity graph               | Separate owner                             |
| SCIM/provisioning/identity-sync configuration | SCIM stores, lifecycle store, provisioning graph, SCIM runtime graph                                                            | Separate owner                             |
| Tenancy/admin/security configuration          | tenant stores, admin elevation, tenant security change graph                                                                    | Separate owner                             |
| Diagnostics/audit/risk/failure mapping        | audit log, correlation id, risk engine, diagnostics, readiness errors                                                           | Cross-cutting owner or small collaborators |
| Final Auth runtime assembly                   | final `Auth`, `Identity`, `Access`, external identity, sync, tenancy objects                                                    | Last assembly owner after smaller slices   |

## 5. Decision

AuthBuilder remains a real BLOCKER. It is not fixed in this phase because splitting 1730 lines of security-sensitive
auth
assembly would be a broad implementation phase, not baseline classification.

The next allowed implementation action is the focused first slice in `07-authbuilder-split-plan.md`.
