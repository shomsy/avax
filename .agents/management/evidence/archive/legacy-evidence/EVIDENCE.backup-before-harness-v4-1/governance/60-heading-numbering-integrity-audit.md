# Heading/Numbering Integrity Audit

## Fixed Issues

| File                                        | Problem                                                              | Severity | Fix                                                   |
|---------------------------------------------|----------------------------------------------------------------------|----------|-------------------------------------------------------|
| `how-to-production-readiness.md`            | Sections 16-29 interleaved with misplaced 20-21 after 29             | HIGH     | Renumbered sequentially 11-27                         |
| `how-to-architecture.md`                    | Canonical Term Registry §54 in wrong place (inside §4 criteria)      | HIGH     | Moved to §27.4 after One Concept One Name             |
| `how-to-architecture-extension-with-ddd.md` | PublicSurface Factory §50, DDD Factory §51 after Final Law           | HIGH     | Moved to §11 and §28.7, all later sections renumbered |
| `how-to-system-security.md`                 | New sections numbered 40-45 conflicting with existing sections 40-45 | HIGH     | Renumbered to 51-55                                   |
| `how-to-system-performance.md`              | New sections numbered 44-45 conflicting with existing 44-45          | HIGH     | Renumbered to 47-48                                   |
| `how-to-design-components.md`               | New sections 22-23 conflicting with existing 22-23                   | HIGH     | Renumbered to 28-29                                   |

## Pre-Existing Issues (Acknowledged, Not Introduced by This Pass)

| File                             | Problem                        | Root cause                                                                                                  |
|----------------------------------|--------------------------------|-------------------------------------------------------------------------------------------------------------|
| `how-to-dependency-injection.md` | Duplicate section 7            | Document evolution inserted Root Application Container Rule at section 7 alongside Container Ownership Rule |
| `how-to-production-readiness.md` | Duplicate sections 8, 9        | Original document had two section 8s and two section 9s from authoring drift                                |
| `how-to-runtime-composition.md`  | Duplicate section 8            | Root Application Container Rule vs Forbidden Contexts — original authoring                                  |
| `how-to-unit-test.md`            | Duplicate section 76           | Gate Self-Test cross-reference inserted after existing content                                              |
| Multiple docs                    | False positive unclosed fences | Deeply nested ` ``` ` fences in code examples confuse simple fence counter                                  |
