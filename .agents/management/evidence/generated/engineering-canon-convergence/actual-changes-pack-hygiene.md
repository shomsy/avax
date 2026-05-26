# Actual Changes Pack Hygiene

Task: Engineering Canon Convergence actual-changes pack hygiene.

## Rule Updated

Updated `.agents/how-to/verification/how-to-create-ai-code-review-packs.md` with an `Actual Changes Review Pack` section.

## Helper Added

Added `tooling/governance/create-actual-changes-review-pack.php`.

## Required Exclusions

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

## Required Included Validation Files

- `validation/tar-list.txt`
- `validation/zip-test.txt`
- `validation/zip-list.txt`
- `validation/expected-engineering-canon-presence.md`

## Known Local Artifact Risk

Top-level archives from a previous pack attempt remain untracked in the worktree and must be skipped by the new packer:

- `2026-05-26-22-36-48-engineering-canon-actual-changes-review.tar.gz`
- `2026-05-26-22-36-48-engineering-canon-actual-changes-review.zip`

## Fresh Pack Result

Command:

```bash
/usr/bin/php8.4 tooling/governance/create-actual-changes-review-pack.php --purpose=engineering-canon --timestamp=2026-05-26-23-20-00
```

Exit code: 0

Output summary:

```text
GREEN: Actual changes review pack created.
pack_folder=/home/shomsy/projects/avax-auth-rewrite-v2/_pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review
tar=/home/shomsy/projects/avax-auth-rewrite-v2/_pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.tar.gz
zip=/home/shomsy/projects/avax-auth-rewrite-v2/_pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.zip
expected_total=47
copied_files=81
skipped_files=2
missing_expected=0
```

Validation commands:

```bash
tar -tzf _pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.tar.gz >/tmp/avax-actual-changes-tar-list-final.txt
unzip -t _pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.zip
unzip -l _pack/2026-05-26-23-20-00-engineering-canon-actual-changes-review.zip >/tmp/avax-actual-changes-zip-list-final.txt
```

Exit codes: 0, 0, 0.

Presence proof:

```text
expected files in report: 47
repo=YES; pack=YES lines: 47
old archive files under files/: 0
```
