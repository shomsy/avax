# AI Review Pack Governance

**Date:** 2026-05-25
**Tool:** `tooling/governance/generate-review-packs.php`
**Scope:** Review pack generation, staging cleanup, manifest validation
**Status:** GREEN

---

## Pack Generator Behavior

The review pack generator creates focused ZIP packages for AI upload.

### What It Does

1. **Auto-clean staging**: Deletes any existing staging directory before generation
2. **Deterministic output**: Uses timestamp-first naming for natural sorting
3. **Duplicate detection**: Reports files that appear in multiple pack definitions
4. **Stale pack detection**: Identifies packs older than 30 days
5. **Manifest validation**: Writes `manifest.json` with generation metadata
6. **ZIP content validation**: Verifies each ZIP contains REVIEW_CONTEXT.md, TREE.txt, STATS.md
7. **Size enforcement**: Warns if any ZIP exceeds 25MB target
8. **Auto-cleanup staging**: Deletes staging directory after ZIP creation

### Pack Definitions

| ZIP | Purpose | File Count | Context |
|-----|---------|-----------|---------|
| `review-01-governance-architecture.zip` | Governance docs, skills, templates, AGENTS.md | Governance quality |
| `review-02-identity-component.zip` | Identity component + tests + shared abstractions | Identity architecture |
| `review-03-framework-core.zip` | Framework + DI surface + HTTP capabilities | Framework runtime |
| `review-04-governance-tooling.zip` | All governance/security/performance tooling | Governance enforcement |
| `review-05-testing-strategy.zip` | Full test suite + phpunit + composer | Testing quality |
| `review-06-self-explaining-architecture.zip` | Self-explaining architecture docs + examples | Documentation philosophy |

### Each ZIP Contains

- Source files (as defined in pack definition)
- `REVIEW_CONTEXT.md` — structured review guidance for the specific context
- `TREE.txt` — complete file listing of the ZIP contents
- `STATS.md` — file counts, LOC, coverage areas, known YELLOW areas

### Validation

Each ZIP is validated after creation:
- ZIP can be opened
- Required metadata files exist (REVIEW_CONTEXT.md, TREE.txt, STATS.md)
- File count is non-zero
- Size is under 25MB (warning if exceeded)

### Multi-AI Review Strategy

Review packs support multi-AI review (ChatGPT, Gemini, Perplexity, Claude, Codex):
- Each AI has different strengths (ChatGPT: broad knowledge, Gemini: code analysis, Claude: reasoning, etc.)
- Same pack can be uploaded to multiple AIs for second opinions
- REVIEW_CONTEXT.md provides consistent context across all AI reviews
- TREE.txt helps AIs understand the package structure

### Stale Pack Policy

Packs older than 30 days are flagged as stale during generation. Stale packs should be:
- Deleted if no longer needed
- Regenerated if still relevant
- Not relied upon for current architectural decisions
