# Governance Document Structure Cleanup

## Moved Sections

| File                                        | Misplaced section               | Old location                             | New location                                 | Why                                                                             |
|---------------------------------------------|---------------------------------|------------------------------------------|----------------------------------------------|---------------------------------------------------------------------------------|
| `how-to-architecture.md`                    | Canonical Term Registry         | After §4 criteria (line 185, section 54) | After §27.3 One Concept One Name (new §27.4) | Was wedged between Core Architectural Law criteria; now lives near naming rules |
| `how-to-architecture-extension-with-ddd.md` | PublicSurface Factory Boundary  | §50 after Final Law                      | §11 inside PublicSurface section             | Belongs in PublicSurface rules, not as appendix                                 |
| `how-to-architecture-extension-with-ddd.md` | DDD Factory vs Runtime Assembly | §51 after Final Law                      | §28.6a inside Factory tactical DDD           | Belongs in Factory rules, not as appendix                                       |

## Numbering Fixed

| File                             | Problem                                                           | Fix                            |
|----------------------------------|-------------------------------------------------------------------|--------------------------------|
| `how-to-production-readiness.md` | Sections numbered 16-29 interleaved with misplaced 20-21 after 29 | Renumbered sequentially: 11-27 |
