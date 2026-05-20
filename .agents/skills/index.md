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
| radi sto vise, nastavi sam, maximum sweep, autonomous backlog loop   | avax-autonomous-backlog-loop|
| production-code change, architecture change, refactor, OOP, SOLID,   | avax-enterprise-codecraft   |
| cohesion, coupling, HLD, LLD, system design, 11++, enterprise quality|                            |
| component change, architecture cleanup, dogfooding, AvaX eats AvaX,  | avax-component-dogfooding   |
| filesystem/cache/logging/events/config/security/runtime/HTTP work    |                            |

## Skill Files

| Skill                      | Location                                                      | Purpose                           |
|----------------------------|---------------------------------------------------------------|-----------------------------------|
| AvaX Enterprise Remediation| `.agents/skills/avax-enterprise-remediation/SKILL.md`         | Master skill for AI-assisted tasks|
| AvaX Autonomous Backlog Loop| `.agents/skills/avax-autonomous-backlog-loop/SKILL.md`       | Autonomous task-by-task execution |
| AvaX Enterprise Codecraft   | `.agents/skills/avax-enterprise-codecraft/SKILL.md`          | HLD/LLD/OOP/SOLID/design quality  |
| AvaX Component Dogfooding   | `.agents/skills/avax-component-dogfooding/SKILL.md`          | component reuse/platform coherence|
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