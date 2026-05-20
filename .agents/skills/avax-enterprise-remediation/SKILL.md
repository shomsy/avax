# Skill: AvaX Enterprise Remediation

## Purpose

This skill is the operational bootloader for every AvaX implementation, remediation, refactor, verification, review, or fix-this.md task.

It forces AI execution to follow AvaX governance, strict how-to rules, advanced OOP expectations, evidence discipline, and pre-commit validation.

Speed is useful only if quality is preserved.

A fast low-quality patch is failure.

## Bootloader Rule

This is a bootloader skill.

For autonomous backlog execution, this skill must route to `avax-autonomous-backlog-loop`.

Agents must not use only this skill when task-specific skills exist.

Agents must discover and apply all task-relevant `.agents` resources including skills, how-to rules, learning, memory, evidence, TODO.md, and fix-this.md.

This skill composes with other skills when the task matches their scope.

## Trigger

Use this skill when the user asks for:

- implement this
- fix this
- remediate this
- refactor this
- verify this
- fix-this.md task
- assigned TODO execution
- enterprise remediation
- AI-assisted execution on AvaX

This is the master skill for bounded AI work on AvaX.

It composes with other skills when the task matches their scope.

## Must Read

Before touching code, read in this order:

1. `AGENTS.md`
2. `.agents/GOVERNANCE_INDEX.md` if present
3. `.agents/how-to/how-to-use-ai-assisted-execution.md`
4. all applicable `.agents/how-to/*.md`
5. `fix-this.md`
6. assigned TODO section from `fix-this.md`
7. `.agents/management/evidence/generated/review-reconciliation/source-inventory.md`
8. `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
9. `.agents/management/evidence/generated/review-reconciliation/source-finding-coverage.md`
10. `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
11. relevant discipline-review and dual-review evidence
12. `.agents/management/learning/**` if present
13. `.agents/management/memory/**` if present
14. `.agents/skills/**` relevant to the task
15. `.agents/skills/index.md`

If any required file is missing, report:

- expected path
- actual result
- impact on confidence
- whether task can continue

No context, no code.

## Required Evidence Before Implementation

Before implementation, write this section in the task evidence:

```md
## Agent Context Loaded

- AGENTS.md read: YES/NO
- .agents/AGENTS.md read: YES/NO/ABSENT
- how-to-ai-assisted-execution.md read: YES/NO
- how-to files discovered: <count>
- how-to files read: <count>
- skills discovered: <count>
- skills used: <list or NONE>
- memory/learning files discovered: <count>
- memory/learning files used: <list or NONE>
- project truth files read: <list>
- review evidence read: <list>
- assigned fix-this TODO: <TODO-ID>
- source clusters read: <cluster IDs>
- source finding IDs read: <IDs>
- applicable governance warnings: <short list>
- accepted YELLOW constraints: <short list>
- pre-existing dirty files: <classification>
```

If this section is missing, the task is invalid.

## Hard Execution Rules

Every task must obey:

- one bounded TODO per execution task
- no unrelated cleanup
- no feature work
- no roadmap work
- no public API changes unless explicitly approved
- no broad suppressions
- no fake GREEN
- no markdown-only closure
- no generated dumps committed
- no production code before context loading
- no commit without tests, validation, governance review, and evidence

## Advanced OOP Standard

All code must be advanced OOP, not cheap OOP.

Required:

- class name says responsibility
- method name says exact action
- folder says flow or capability
- objects protect invariants
- value objects are meaningful
- factories exist only when creation has rules
- result objects are typed and explicit
- PublicSurface is small and delegates
- Configuration assembles
- Flows execute
- Capabilities provide reusable ability
- Foundation stays tiny and neutral
- Runtime executes, not assembles

Reject:

- fake abstractions
- empty wrappers
- generic Service/Manager/Helper/Util dumping grounds
- interfaces with no real variance
- arrays where typed result objects are expected
- moving code into smaller classes without clearer ownership
- clever DSL that is not obvious

## Security and Runtime Rules

Security findings are HIGH/BLOCKER by default.

Treat these as blocking unless proven safe:

- unsafe deserialization
- auth/session/CSRF boundaries
- secrets
- logging sensitive data
- redirects
- file paths
- command execution
- dynamic class loading
- crypto
- token/cookie handling
- runtime state leakage
- static mutable request/user/session/security state

Long-lived worker safety must be considered for every runtime/public surface change.

## Pre-Commit Governance Loop

Before commit:

1. implement/fix
2. run focused tests
3. run required validation
4. run relevant governance gates
5. perform governance review against applicable how-to rules
6. classify findings:

   - BLOCKER
   - HIGH
   - MEDIUM
   - LOW
   - ACCEPTED_YELLOW
   - ACCEPTED_EXCEPTION
7. fix every BLOCKER/HIGH/MEDIUM
8. rerun validation
9. rerun governance review
10. write evidence
11. inspect staged files
12. commit only if clean

Commit is forbidden if:

- tests fail
- PHPStan fails
- required gates fail
- BLOCKER/HIGH/MEDIUM remains
- security HIGH/BLOCKER remains
- public API changed without approval
- evidence missing
- context loading evidence missing
- unrelated files staged
- generated dumps/local files staged
- final status lies

## Worktree Preflight

Run:

```bash
git status --short
git diff --stat
```

Classify dirty files:

- CLEAN
- PRE_EXISTING_RELEVANT
- PRE_EXISTING_UNRELATED
- GENERATED_NOISE
- BLOCKER

Stop if unrelated production/test/composer/autoload files are dirty.

Do not stage:

- `avax.part-*`
- `.codex`
- screenshots
- IDE/editor files
- temporary text files
- unrelated generated evidence
- local cache files
- aggregate dumps

## Required Output

Return:

1. assigned TODO
2. worktree preflight status
3. context loaded summary
4. files inspected
5. files changed
6. design decision summary
7. advanced OOP quality notes
8. tests added/updated
9. validation output
10. governance review findings table
11. evidence path
12. remaining YELLOW if any
13. commit hash or reason no commit was made
14. final decision:

- TODO_CLOSED
- TODO_PARTIAL_WITH_YELLOW
- TODO_BLOCKED

15. one-sentence reason

## Final Rule

No context, no code.

No evidence, no commit.

No tests, no trust.

No governance review, no GREEN.

No advanced OOP, no merge.
