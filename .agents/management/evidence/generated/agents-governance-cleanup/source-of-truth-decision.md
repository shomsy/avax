# Source Of Truth Decision

Date: 2026-05-26
Branch: architecture/identity-runtime-convergence
Task: Bring `.agents` governance loading into order.

## Sources Loaded

| Source | Status | Decision |
|---|---|---|
| `AGENTS.md` | Loaded from conversation context and inspected for `how-to.txt` staging rule | Root contract wins. |
| `.agents/how-to/README.md` | Loaded | Canonical structure map for `.agents/how-to/`. |
| `.agents/how-to/00-reading-order.md` | Renamed | User requested rename to `.agents/how-to/00-how-to-reading-order.md`. |
| `.agents/skills/self-explaining-architecture/SKILL.md` | Loaded | Stale root how-to reference needed correction. |
| Active `.agents/how-to/**/*.md` | Searched | Stale root how-to references needed correction. |
| Governance tooling | Searched | Checkers needed new reading-order filename. |

## Final Truth

- `.agents/how-to/00-how-to-reading-order.md` is the canonical reading-order file.
- `.agents/how-to/00-reading-order.md` is no longer a valid active path.
- Root `.agents/how-to/` contains only `README.md` and `00-how-to-reading-order.md`.
- `how-to.txt` is retired as a repository file. If tooling recreates it locally, it remains ignored and non-canonical.
- Historical generated evidence may still mention old filenames as historical output; active rules, skills, and tooling must not.

## Risk

Remaining risk is limited to historical evidence mentioning old names. That evidence is not canonical governance.
