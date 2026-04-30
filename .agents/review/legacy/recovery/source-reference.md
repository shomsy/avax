# AvaX Feature Recovery Source Reference

## Primary Sources

1. **avax-backup.txt** — 1,126,319 lines. Primary recovery source of truth.
2. **avax.txt** — 1,541,291 lines (56MB). Secondary recovery source.
3. **main git branch** — Reference for old implementations.

## Rules

- [x] `avax-backup.txt` is NOT production source.
- [x] `avax-backup.txt` is NOT autoloaded.
- [x] `avax-backup.txt` is NOT deleted until all recovery matrices are closed.
- [x] Architecture checkers must ignore `avax-backup.txt` as source, but recovery scripts may read it.

## Location

Original files:

- `/home/shomsy/projects/avax/avax-backup.txt`
- `/home/shomsy/projects/avax/avax.txt`
- Git branch: `main`
