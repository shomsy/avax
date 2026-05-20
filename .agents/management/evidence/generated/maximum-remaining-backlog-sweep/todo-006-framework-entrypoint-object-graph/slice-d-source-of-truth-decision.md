# TODO-006 Slice D Source Of Truth Decision

## Scope

- **Active TODO**: `TODO-006` (framework public entrypoint object-graph assembly)
- **Active Slice**: Slice D (`Avax.php` assembly cleanup)
- **Branch**: `architecture/todo-006-framework-entrypoint-object-graph-slice-d`
- **Worktree**: `/home/shomsy/projects/avax-todo-006-d`

## Authorities

- **AGENTS.md**: Version 3.0.0 (Root execution contract).
- **Current Git State**: Checked out cleanly at `166aca8e5` in worktree `/home/shomsy/projects/avax-todo-006-d`.
- **Preceding Slices**: Slice A, Slice B, and Slice C merged. Residual findings exist for `Avax.php`.

## Reconciled Decisions

- **Avax.php Exception**: Even though `Avax.php` is listed as an approved composition root in `check-direct-instantiation.php` to prevent gate failure, keeping massive object-graph instantiations inside `PublicSurface/Avax.php` violates our core architecture rules.
- **Resolution**: Refactoring `Avax.php` to delegate its construction tasks to `Configuration/BuildAvaxEngine.php` is 100% architecturally correct and aligns with V4 screaming architecture patterns.
- **Risk**: Very LOW. The public API surface is fully preserved.
