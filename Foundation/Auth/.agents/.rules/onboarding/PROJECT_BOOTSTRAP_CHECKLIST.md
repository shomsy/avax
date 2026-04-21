# Project Bootstrap Checklist

Use this when initializing `.agents` in a new repository.

## Checklist

- copy reusable `.agents` structure
- copy or sync `merge-files.sh` from the OS source and keep the latest version
- rewrite `business-logic/` for target domain
- declare the applied governance stack in root `AGENTS.md`
- adopt the timestamp format and estimation law from `management/TIMELINE.md`
- choose relevant architecture overlays under
  `governance/architecture/profiles/**`
- choose relevant coding profiles under `governance/profiles/**`
- decide whether `governance/security/**` is mandatory on day one for the
  target repository
- adapt `governance/architecture/ARCHITECTURE.md` to real repo boundaries
- make sure root `AGENTS.md` and child `ARCHITECTURE.md` agree on the applied
  stack and slice model
- confirm quality questions fit product context
- initialize canonical management files
- decide whether runtime operations docs under `governance/delivery/operations/` are needed on day one
- bind validation, smoke, rollback, and recovery entrypoints in root `AGENTS.md`
- review release and rollback policy fit
- decide whether the repo needs strict review as a formal release gate
- confirm review output contract
- validate templates and glossary language for team usage
