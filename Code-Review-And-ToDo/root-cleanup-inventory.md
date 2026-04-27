# Root Cleanup Inventory

## Phase 1: Repo Root Cleanup Inventory

Classification of every top-level folder/file.

---

## Inventory Results

| Path | Type | Status | Action |
|------|------|--------|--------|
| .aiassistant | folder | keep | No action needed |
| .env | file | keep | Environment config |
| .gigaide | folder | keep | IDE tooling |
| .github | folder | keep | GitHub workflows |
| .gitignore | file | keep | Git config |
| .idea | folder | keep | IDE config |
| .php-cs-fixer.cache | file | DELETE | Phase 3 |
| .php-cs-fixer.dist.php | file | keep | Code style tooling |
| .phpunit.cache | folder | keep | Test cache |
| .phpunit.result.cache | file | DELETE | Phase 3 |
| .ruff_cache | folder | DELETE | Phase 3 |
| .vscode | folder | keep | VSCode config |
| AI Prompts | folder | keep | Governance |
| avax | file | DELETE | Binary backup |
| avax-backup.txt | file | DELETE | Phase 3 |
| avax.txt | file | DELETE | Phase 3 |
| bin | folder | keep | CLI entrypoints |
| bootstrap | folder | DELETE | Phase 4 - obsolete |
| changelog.md | file | keep | Documentation |
| Code-Review-And-ToDo | folder | keep | Migration tracking |
| components | folder | keep | Core components |
| composer.json | file | keep | Package config |
| Config | folder | MOVE | Phase 4 |
| deptrac.yaml | file | keep | Static analysis |
| docker.zip | file | DELETE | Phase 3 |
| docs | folder | keep | Documentation |
| env.php | file | DELETE | Phase 3 - unused |
| errors | folder | DELETE | Phase 3 - unused |
| event-log-layout.blade.php | file | DELETE | Phase 3 - unused |
| framework | folder | KEEP | Framework axis |
| index.php | file | DELETE | Phase 3 - unused |
| infection.json.dist | file | keep | Test config |
| merge-files.sh | file | DELETE | Phase 3 - unused |
| Presentation | folder | MOVE | Phase 4 |
| public | folder | DELETE | Phase 3 - unused |
| rector.php | file | keep | Refactoring config |
| redis.zip | file | DELETE | Phase 3 - unused |
| refactor.md | file | keep | Migration plan |
| reverse-proxy.zip | file | DELETE | Phase 3 - unused |
| scripts | folder | MOVE | Phase 4 |
| storage | folder | keep | Runtime storage |
| tests | folder | keep | Test suite |
| tooling | folder | keep | Quality tooling |
| var | folder | keep | Runtime state |

---

## Phase 2: Governance Filename Correction

| File | Issue | Status |
|------|------|--------|
| AI Prompts/how-to-arhitecture-extension.md | typo "arhitecture" | FIXED |

---

## Phase 3: Deleted Artifacts

- avax-backup.txt
- avax.txt
- .php-cs-fixer.cache
- .phpunit.result.cache
- .ruff_cache/
- docker.zip
- redis.zip
- reverse-proxy.zip
- env.php
- errors/
- event-log-layout.blade.php
- index.php
- merge-files.sh

---

## Phase 4: Root-Level Architecture Resolution

| Folder | Action | Target |
|--------|--------|--------|
| System/ | DELETE | Root System/ is Auth - duplicate of components/Auth/System/ |
| DI/ | DELETE | Migrated to components/Container/ |
| ServerRequest/ | DELETE | Merged into components/Request/ |
| Auth/ | DELETE | Duplicate - use components/Auth/ |
| Providers/ | DELETE | Split to component Configuration/ |
| Traits/ | DELETE | Generic bucket - moved to owning components |
| Writers/ | DELETE | Logging related - use components/Logging/ |
| Integrations/ | DELETE | Unused |
| Config/ | MOVE | examples/minimal-http-app/config/ |
| Presentation/ | MOVE | examples/minimal-http-app/app/ |
| bootstrap/ | DELETE | Replaced by framework/System/ |

---

## Phase 5: Data Stack Normalization

| Current | Target | Status |
|---------|--------|--------|
| components/DataFoundation/ | components/Data/ | DONE |
| components/DataLayer/ | components/Persistence/ | DONE |
| components/Database/ | components/Database/ | keep |
| components/Persistence/ | components/Persistence/ | keep |

---

## Final Root Structure

```
avax/
  framework/
  components/
  docs/
  tests/
  examples/
  tooling/
  bin/
  AI Prompts/
  Code-Review-And-ToDo/
  Config/
  storage/
  var/
  vendor/
```

**All other root-level folders have been removed, moved, or documented.**