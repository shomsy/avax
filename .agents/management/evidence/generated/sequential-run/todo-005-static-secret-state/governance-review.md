# Governance Review — TODO-005 Static Secret State

## Applicable Governance Documents

| Document | Status |
|---|---|
| AGENTS.md | READ |
| .agents/how-to/how-to-system-security.md | READ — static mutable security state is BLOCKER |
| .agents/how-to/how-to-clean-code.md | READ |
| .agents/how-to/how-to-coding-standards.md | READ |
| .agents/how-to/how-to-use-ai-assisted-execution.md | READ |

## Findings Table

| Finding | Severity | File | Problem | Resolution |
|---|---|---|---|---|
| Static mutable secret store | BLOCKER | Secrets.php | `$secretStore` held statically, never cleared between worker requests | `Secrets::reset()` added; wired into StaticStateReset step 5 |
| Reset lifecycle gap | HIGH | StaticStateReset.php | Secrets not included in worker reset cycle | Added as step 5 between ExternalState and ShutdownSequence |
| GlobalEventListenerState reset gap | MEDIUM | StaticStateReset.php | Not wired into reset cycle | Out of scope — documented in threat-analysis residual risks |
| StatelessBoundary::$mode | LOW | N/A | Configuration set once at boot | Not mutable per-request; acceptable |

## Compliance Matrix

| Rule | Status |
|---|---|
| Folder says flow/capability | PASS — PublicSurface/Secrets.php |
| No forbidden folder names | PASS |
| Advanced OOP: class says responsibility | PASS — Secrets owns secret storage facade |
| No cheap OOP wrappers | PASS — reset() is real behavior |
| Security fails closed | PASS — reset creates fresh store, no degraded fallback |
| Long-lived worker safety | PASS — reset wired into WorkerLoop lifecycle |
| Tests prove behavior | PASS — 5 new security tests |

## Decision

Governance review complete. No BLOCKER or HIGH findings remain. MEDIUM finding (GlobalEventListenerState) is documented as residual risk with separate scope.
