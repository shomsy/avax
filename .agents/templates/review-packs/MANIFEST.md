# Manifest — AI Code Review Packs

Generated: {YYYY-MM-DD HH:MM UTC}
Purpose: {describe review purpose}

## ZIP Files

| File | Size | Files | Included Paths |
|------|------|-------|----------------|
| `{zip-name}.zip` | {size} | {count} | `{path}/`, `{path}/` |
| `{zip-name}.zip` | {size} | {count} | `{path}/`, `{path}/` |

## Excluded Paths (Always)

- `vendor/` — Composer dependencies
- `.git/` — Git history
- `node_modules/` — Node dependencies
- `coverage/` — Test coverage output
- `storage/` — Runtime storage
- `cache/` — Cache directories
- `var/` — Variable data
- `tmp/` — Temporary files
- `.qoder/` — Editor state
- `.idea/`, `.vscode/` — IDE files
- `EVIDENCE/archive/` — Archived evidence
- `docs/reference/` — Reference documentation
- `*.zip`, `*.tar`, `*.gz` — Archive files
- `*.log` — Log files

## Recommended Upload Order

1. `{zip-name}.zip` — {reason}
2. `{zip-name}.zip` — {reason}
3. `{zip-name}.zip` — {reason}

## Known Limitations

- {Limitation 1}
- {Limitation 2}

## Validation

- [ ] `_pack/` is gitignored
- [ ] ZIP integrity verified
- [ ] Each ZIP contains REVIEW_CONTEXT.md, TREE.txt, STATS.md
- [ ] No vendor/.git/node_modules included
- [ ] Package sizes reasonable
- [ ] No secrets included
