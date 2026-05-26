# How to Create AI Code Review Packs

## Purpose

Define a strict reusable rule for creating ZIP review packs for ChatGPT, Gemini, Perplexity, Claude, Qoder, Codex, or any browser/agent AI used for second-opinion code review.

Large repositories cannot always be uploaded as one dump. Focused ZIP review surfaces produce better review quality than raw project dumps.

Review packs are **AI-readable review surfaces**, not backups.

They must be:
- focused
- small enough to upload
- low-noise
- structured
- easy to inspect
- useful for principal/staff-level review
- useful for multiple AI second opinions

## Canonical Output Location

All review exports MUST be created under:

```
_pack/
```

Each export MUST use a timestamped folder:

```
_pack/YYYY-MM-DD-HH-MM-SS-purpose-review/
```

Examples:

```
_pack/2026-05-25-21-44-08-identity-review/
_pack/2026-05-25-22-10-31-governance-review/
_pack/2026-05-25-22-55-12-runtime-review/
_pack/2026-05-25-23-04-00-full-architecture-review/
```

### Naming Rules

- Date/time MUST come first for easy sorting.
- Use 24-hour time.
- Use lowercase kebab-case.
- The final part MUST describe review purpose clearly.
- Prefer simple names:
  - `identity-review`
  - `governance-review`
  - `runtime-review`
  - `testing-review`
  - `security-review`
  - `full-architecture-review`
  - `self-explaining-architecture-review`

## Gitignore Rule

Ensure `.gitignore` contains:

```
_pack/
```

If missing, append it once. Do not duplicate it. Do not commit generated ZIPs.

## Core Principle

Review packs help:

- ChatGPT review large systems
- Gemini/Perplexity/Claude provide second opinions
- Reduce context noise
- Avoid uploading vendor/cache/generated junk
- Preserve architectural context
- Make review repeatable
- Make external AI review safer and cleaner
- Compare multiple AI perspectives on the same bounded package

## Security Rule

Before packaging, verify:

- no secrets included
- no `.env` files included
- no private keys included
- no tokens included
- no production credentials included
- no private customer data included

If secret risk exists: **stop and report**. Do not package.

## Excluded Always

These paths MUST never be included in review packs:

- `vendor/`
- `node_modules/`
- `.git/`
- `coverage/`
- `storage/`
- `cache/`
- `var/`
- `tmp/`
- `.qoder/`
- `.idea/`
- `.vscode/`
- `*.zip`, `*.tar`, `*.gz`
- `*.log`
- generated dumps
- large binary files
- screenshots (unless explicitly needed)
- the archived evidence directory (unless review specifically needs history)
- `docs/reference/` (unless review specifically needs reference implementation)

## Size Rule

Each ZIP SHOULD stay under 25MB.

If larger:
- split by review boundary, not random byte chunks
- prefer smaller focused packs over one large archive
- document split reason in MANIFEST.md

## Required Package Structure

Each export folder SHOULD contain:

```
README.md
MANIFEST.md
review-*.zip
```

Each ZIP inside the folder MUST contain:

```
REVIEW_CONTEXT.md
TREE.txt
STATS.md
```

### REVIEW_CONTEXT.md Requirements

MUST explain:

- purpose of the package
- what is included
- what is intentionally excluded
- what should be reviewed
- known YELLOW areas
- known RED/BLOCKER areas if any
- important governance relationships
- expected future direction
- warnings for AI reviewers

### TREE.txt Requirements

MUST include:

- full directory tree of included files
- enough detail for navigation

### STATS.md Requirements

MUST include:

- file count
- approximate LOC
- key folders
- test counts if applicable
- governance/evidence counts if applicable
- known compromises
- package size
- important architectural areas

## Default Review Packs

### 1. Governance Architecture Pack

**Name:** `review-governance-architecture.zip`

**Include:**

```
.agents/how-to/**
.agents/skills/**
.agents/templates/**
.agents/management/**
docs/architecture/**
docs/development/**
docs/examples/**
AGENTS.md
CURRENT_TRUTH.md
TODO.md
EXECUTION.md
README.md
ARCHITECTURE.md (if present)
tooling/governance/**
```

**Purpose:** Review architecture philosophy, governance quality, AI operating system, documentation, naming rules, ADRs, self-explaining architecture.

### 2. Active Component Pack

**Name:** `review-{component}-component.zip`

**Example:** `review-identity-component.zip`

**Include:**

```
components/{ComponentName}/**
related tests (tests/Unit/Components/{ComponentName}/, tests/Architecture/Components/{ComponentName}/)
local docs (components/{ComponentName}/docs/**, docs/examples/self-explaining-architecture/{component}/)
relevant evidence (EVIDENCE/{component}-*.md)
only necessary shared abstractions
```

**Purpose:** Review actual active implementation — enterprise architecture, boundaries, security thinking, runtime safety, naming quality, DSL quality, folder semantics, coupling, test quality.

### 3. Framework Core Pack

**Name:** `review-framework-core.zip`

**Include:**

```
framework/**
components/Container/System/PublicSurface/
components/HTTP/System/
related tests
```

**Purpose:** Review runtime architecture, lifecycle safety, DI correctness, request scope isolation, long-running runtime readiness, service locator violations, hidden construction, async readiness, enterprise runtime design.

### 4. Governance Tooling Pack

**Name:** `review-governance-tooling.zip`

**Include:**

```
tooling/governance/**
tooling/refactor/**
tooling/components/**
tooling/security/**
tooling/performance/**
tooling/testing/**
tooling/Architecture/**
tooling/audit_*.php
tooling/check_*.php
tooling/phpVersion.php
EVIDENCE/governance-*.md
tooling/governance/baselines/
```

**Purpose:** Review enforcement quality, anti-pattern detection, governance automation, architecture enforcement, validation strategy, CI philosophy, evidence quality.

### 5. Testing Strategy Pack

**Name:** `review-testing-strategy.zip`

**Include:**

```
tests/Unit/**
tests/Integration/**
tests/Feature/**
tests/Architecture/**
tests/GoldenPathRuntime/**
tests/Contract/**
tests/Operations/**
tests/TestCase.php
phpunit.xml
composer.json
composer.lock
EVIDENCE/parallel-testing-*.md
EVIDENCE/parallelism-test-*.md
EVIDENCE/test-speed-*.md
tooling/testing/ (if exists)
```

**Purpose:** Review test philosophy, risk-based coverage, happy/sad paths, fake coverage risk, parallel testing, runtime-safe testing, enterprise confidence model.

### 6. Self-Explaining Architecture Pack

**Name:** `review-self-explaining-architecture.zip`

**Include:**

```
.agents/how-to/documentation/how-to-write-self-explaining-architecture.md
.agents/how-to/documentation/**
.agents/skills/self-explaining-architecture/SKILL.md
.agents/templates/architecture/**
.agents/dictionary/**
docs/examples/self-explaining-architecture/**
.agents/management/evidence/generated/self-explaining-architecture/**
```

**Purpose:** Review dictionary/ADR/Mermaid/local docs philosophy, AI-oriented architecture docs, dictionary governance, ADR strategy, Mermaid usage, maintainability philosophy, anti-tribal-knowledge strategy.

## Folder README.md Requirements

The export folder `README.md` MUST include:

- generated timestamp
- purpose
- package list
- package sizes
- recommended upload order
- what to ask AI reviewer
- known YELLOW areas
- known RED/BLOCKER areas
- excluded paths
- warning that packs are review surfaces, not backups

## MANIFEST.md Requirements

MUST include:

- exact ZIP names
- file counts
- sizes
- included top-level paths
- excluded paths
- recommended upload order
- known limitations

## Multi-AI Review Rule

The same packs may be uploaded to multiple AI systems when useful:

- **ChatGPT** — deep architecture/code reasoning
- **Gemini** — alternative architecture perspective
- **Perplexity** — external research/source-backed comparison
- **Claude** — documentation/readability review
- **Codex/Qoder** — repo-local execution/validation

**AI opinions are advisory.**
Repository tests, governance, security rules, and source-of-truth documents remain authoritative.

## Validation Checklist

Before declaring review packs complete:

- [ ] `_pack/` is gitignored
- [ ] ZIP integrity verified (`unzip -t` passes)
- [ ] each ZIP contains REVIEW_CONTEXT.md, TREE.txt, STATS.md
- [ ] no vendor/.git/node_modules included
- [ ] package sizes reasonable (under 25MB each)
- [ ] README.md exists in export folder
- [ ] MANIFEST.md exists in export folder
- [ ] no secrets included (checked .env, tokens, keys)
- [ ] `git status` shows only expected governance/doc changes tracked, not generated packs

## Cleanup Rules

### Staging Directory Cleanup

All staging directories MUST be deleted after ZIP creation.

```text
Pattern: _pack/*/staging-*/
Action: rm -rf after ZIP is created
Reason: Staging dirs are implementation artifacts, not review surfaces
```

### Failed Attempt Cleanup

Failed, abandoned, or botched generation attempts MUST be cleaned up immediately.

Valid, successful packs from previous generations MUST be preserved.
They serve as development history and potential backup.

```text
Pattern: _pack/*/ (multiple timestamped folders)
Keep:    Valid packs from any previous successful generation.
Delete:  Failed, abandoned, superseded, or botched attempts only.
Test:    If a folder contains broken/incomplete archives, missing manifests,
         or was created by a process that errored out — delete it.
         If a folder contains complete, valid archives — keep it.
Trigger: After completing a successful generation.
Reason:  Failed attempts are noise. Valid historical packs are backup.
```

To distinguish a valid pack from a failed one:

```text
Valid pack:
  - Contains README.md, MANIFEST.md
  - All review-*.tar.gz files pass integrity check
  - STATS.md and REVIEW_CONTEXT.md present in every archive
  - No staging-*/ directories linger

Failed pack:
  - Missing README.md, MANIFEST.md, or manifest.json
  - Archives missing or truncated
  - Staging directories still present
  - Generation script exited with error
```

If a pack's validity cannot be determined, classify it as UNKNOWN and report as YELLOW.

If failed packs cannot be deleted due to permission issues, the agent MUST:
1. Report the problem as a YELLOW finding
2. Attempt recovery via elevated permissions or PHP recursive deletion
3. If unresolved, document the remaining stale paths in evidence

This rule exists because a previous agent left 7 failed-attempt folders in `_pack/`
after a generation session. The folders were root-owned, undelatable, and indistinguishable
from valid packs without inspection.

### Stale Pack Detection

Packs older than 30 days SHOULD be reviewed for staleness.

```text
Find stale packs:
  find _pack/ -maxdepth 1 -type d -mtime +30

Stale packs are NOT automatically deleted.
They remain for historical reference but should not be uploaded.
```

### Regeneration Rules

When source code changes significantly, review packs MUST be regenerated:

```text
Trigger: Component structure changed, tests added/removed, governance updated
Action: Create new timestamped folder, do NOT overwrite previous
Previous packs: Remain for historical comparison
```

Never overwrite a previous export. Always create a new timestamped folder.

## Manifest Validation

The MANIFEST.md MUST accurately describe ZIP contents.

### Required Validation

After creating review packs, verify:

```bash
# 1. ZIP integrity
unzip -t _pack/*/review-*.zip

# 2. Metadata files exist in each ZIP
for zip in _pack/*/review-*.zip; do
  unzip -l "$zip" | grep -E "REVIEW_CONTEXT.md|TREE.txt|STATS.md"
done

# 3. No forbidden directories
for zip in _pack/*/review-*.zip; do
  unzip -l "$zip" | grep -E "vendor/|\.git/|node_modules/"
done

# 4. Manifest matches actual file counts
for zip in _pack/*/review-*.zip; do
  actual=$(unzip -l "$zip" | tail -1 | awk '{print $2}')
  manifest=$(grep "$(basename $zip)" MANIFEST.md | grep -oP '\d+(?=\s+files)' || echo "NOT_FOUND")
  echo "$(basename $zip): ZIP=$actual MANIFEST=$manifest"
done

# 5. Manifest claims match ZIP content (e.g., test file counts)
unzip -l review-identity-component.zip | grep 'tests/Unit/.*\.php' | wc -l
```

### Manifest Accuracy Rule

If MANIFEST.md claims a ZIP contains files that it does not actually contain, **the pack is INVALID**.

This is a BLOCKER governance violation: false evidence is worse than no evidence.

## Timestamp Sorting Explanation

Folders in `_pack/` sort naturally by timestamp:

```bash
ls _pack/ | sort

# Output:
# 2026-05-25-21-44-08-identity-review/
# 2026-05-25-22-10-31-governance-review/
# 2026-05-26-09-15-00-runtime-review/
```

The most recent pack for a given purpose is the last one when sorted.

## Multi-AI Review Strategy

The same packs may be uploaded to multiple AI systems:

| AI | Strength |
|----|----------|
| ChatGPT | Deep architecture/code reasoning |
| Gemini | Alternative architecture perspective |
| Perplexity | External research/source-backed comparison |
| Claude | Documentation/readability review |
| Codex/Qoder | Repo-local execution/validation |

**AI opinions are advisory.**
Repository tests, governance, security rules, and source-of-truth documents remain authoritative.

## Forbidden

- Do not treat ZIP packs as backups.
- Do not include `vendor/`.
- Do not include secrets.
- Do not include massive dumps by default.
- Do not commit generated packs.
- Do not create vague package names like `files.zip` or `project.zip`.
- Do not overwrite previous exports unless explicitly requested.
- Do not delete unrelated `_pack/` folders.

## Recommended Upload Order

1. `review-governance-architecture.zip` — establishes context
2. `review-{component}-component.zip` — the active work
3. `review-framework-core.zip` — the foundation
4. `review-governance-tooling.zip` — the enforcement
5. `review-testing-strategy.zip` — the proof
6. `review-self-explaining-architecture.zip` — the documentation

## Actual Changes Review Pack

Use an actual-changes review pack when the standard review packs are structurally valid but do not contain the real current working-tree delta. This pack is for human review of changed tracked files, staged files, and untracked files.

This pack is not canonical governance and does not replace normal review packs.

Required structure:

```text
_pack/YYYY-MM-DD-HH-MM-SS-<purpose>-actual-changes-review/
  metadata/
  files/
  patches/
  validation/
```

Required metadata:

- `metadata/README.md`
- `metadata/git-status-short.txt`
- `metadata/git-status-porcelain-v1.txt`
- `metadata/git-diff-name-only.txt`
- `metadata/git-diff-cached-name-only.txt`
- `metadata/git-untracked-files.txt`
- `metadata/all-changed-and-untracked-files.txt`
- `metadata/copied-files.txt`
- `metadata/skipped-files.txt`
- `metadata/deleted-files.txt`
- `metadata/repo-info.txt`
- `metadata/tree-files.txt`
- `metadata/sha256sums.txt`

Required patches:

- `patches/git-diff.patch`
- `patches/git-diff-cached.patch`
- `patches/git-diff-stat.txt`
- `patches/git-diff-cached-stat.txt`

Required validation files:

- `validation/tar-list.txt`
- `validation/zip-test.txt`
- `validation/zip-list.txt`
- `validation/expected-engineering-canon-presence.md` when reviewing Engineering Canon work

The pack must include untracked files unless excluded by safety rules.

Always exclude:

```text
_pack/**
vendor/**
.git/**
node_modules/**
cache/**
coverage/**
tmp/**
.qoder/**
.env
*.pem
*.key
*.crt
*.p12
*.pfx
*engineering-canon-actual-changes-review*.zip
*engineering-canon-actual-changes-review*.tar.gz
```

Do not copy prior generated review archives into `files/`.

The pack must create and validate both:

```text
_pack/<run-id>.tar.gz
_pack/<run-id>.zip
```

Validation must prove:

- tar archive lists successfully;
- zip archive tests successfully;
- copied files list is generated from `files/`;
- skipped files list names every excluded changed/untracked path;
- expected required files exist in repo and in the pack;
- archive paths are reported exactly.

## Future Tooling

A future automated tool should be created at:

```
tooling/review/create-ai-review-packs.sh
```

or

```
tooling/review/create-ai-review-packs.php
```

The tool should:

- accept review purpose as argument
- create timestamped folder under `_pack/`
- generate selected review packs
- generate README.md and MANIFEST.md
- validate exclusions (no vendor, no secrets, no junk)
- ensure `_pack/` is gitignored
- validate ZIP integrity
- verify each ZIP contains REVIEW_CONTEXT.md, TREE.txt, STATS.md

## Related

- `.agents/templates/review-packs/` — templates for REVIEW_CONTEXT.md, STATS.md, README.md, MANIFEST.md
- `.agents/skills/review/SKILL.md` — code review skill
- `.agents/how-to/verification/how-to-code-review.md` — code review process
- AGENTS.md §13 — Final Output Contract
