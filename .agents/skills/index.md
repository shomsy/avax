# Skills Index

This file routes tasks to the correct skill.

## Task to Skill Mapping

| User Request Contains                                                | Skill                      |
|----------------------------------------------------------------------|----------------------------|
| implement, fix, remediate, fix-this, assigned TODO, AI-assisted      | avax-enterprise-remediation|
| recover, restore, old backup, Framework.txt, Components.txt          | recovery                   |
| validate, prove green, check, verify, is it ready                    | validation                 |
| review, audit, evaluate, analyze                                     | review                     |
| rename, move, split, merge, refactor, reorganize                     | refactor                   |
| test, coverage, unittest, phpunit, mock                              | testing                    |
| security, authentication, authorization, encryption, password, token | security                   |
| performance, benchmark, optimization, speed, latency                 | performance                |
| docs, documentation, PHPDoc, how-this-works                          | documentation              |

## Skill Files

| Skill                      | Location                                                      | Purpose                           |
|----------------------------|---------------------------------------------------------------|-----------------------------------|
| AvaX Enterprise Remediation| `.agents/skills/avax-enterprise-remediation/SKILL.md`         | Master skill for AI-assisted tasks|
| Recovery                   | `.agents/skills/recovery/SKILL.md`                            | Restore old behavior              |
| Validation                 | `.agents/skills/validation/SKILL.md`                          | Prove code is green               |
| Review                     | `.agents/skills/review/SKILL.md`                              | Systematic review                 |
| Refactor                   | `.agents/skills/refactor/SKILL.md`                            | Rename, move, split               |
| Testing                    | `.agents/skills/testing/SKILL.md`                             | Test creation/repair              |
| Security                   | `.agents/skills/security/SKILL.md`                            | Security work                     |
| Performance                | `.agents/skills/performance/SKILL.md`                         | Performance work                  |
| Documentation              | `.agents/skills/documentation/SKILL.md`                       | Docs generation                   |

## Hard Rule

If a task matches a local skill, the agent must read the matching skill before editing files.