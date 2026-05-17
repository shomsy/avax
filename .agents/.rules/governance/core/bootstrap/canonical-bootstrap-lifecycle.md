---
owner: governance-core
status: active
machine-enforced: true
---

# Canonical Bootstrap Lifecycle

Version: 3.0.0
Status: Normative

This is the canonical lifecycle flow an AI agent MUST traverse when invoked in an Agent Harness V3 repository.

## 1. Discovery Phase (Read-Only)
1. Read `/AGENTS.md` (Local root overrides).
2. Read `/.agents/config/project.json` (Machine-readable overrides).
3. Read `/.agents/AGENTS.md` (Global OS contract and version).

## 2. Context Loading Phase (Read-Only)
1. Resolve Governance Stack (`profile-resolution-algorithm.md`).
2. Load required profiles (Languages, Project Types, Frameworks).
3. Read Management State:
   - `CURRENT.md` (What is true right now)
   - `ACTIVE.md` / `TODO.md` (Current work assigned)
   - `STATUS.md` (Current system health)
   - `RISKS.md` (Accepted technical debt)
4. Read recent Evidence (`EVIDENCE/CHANGELOG.md` or `.agents/management/evidence/phases/`).

## 3. Planning Phase
1. Output deterministic plan mapping task to resolved governance rules.
2. If task violates a BLOCKER or HIGH security/architecture policy, halt and require human approval.

## 4. Execution Phase (Execute Mode)
1. Perform file modifications strictly within the bounds of the task.
2. Adhere to Profile rules (e.g., Go formatting, PHP standards, React folder structures).

## 5. Verification Phase (Validation)
1. Run Canonical Validation (`npm test`, `go test`, `./verify.sh`).
2. Run Quality Gates (`quality-gates.md`).
3. Store raw validation output in `.agents/management/evidence/validation/`.

## 6. Recursive Review Phase (Self-Healing)
1. Run Recursive Governance Review (`recursive-review-contract.md`).
2. Check changes against Security, Architecture, Coding standards.
3. If findings (BLOCKER/HIGH/MEDIUM): FIX -> REVALIDATE -> RE-REVIEW.
4. If findings (YELLOW/debt): Document in `RISKS.md` and explicitly ACCEPT.

## 7. Evidence Generation Phase
1. Record granular proof of work to `.agents/management/evidence/`.
2. Update high-level `EVIDENCE/` human dashboard (CURRENT, CHANGELOG).
3. Update Management queues (close TODO, update STATUS).

## 8. Finalization
1. Recommend human commit or release execution.
2. End session.
