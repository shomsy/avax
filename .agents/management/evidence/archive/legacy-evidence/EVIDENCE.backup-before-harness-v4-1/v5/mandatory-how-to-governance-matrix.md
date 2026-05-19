# Mandatory how-to Governance Matrix — V5-01

Date: 2026-05-10
Stage: V5-01 Whole-Repo Governance Resolution

## Purpose

Map every how-to document to every V5 stage. If a document does not apply, say why.

## Matrix

| Governance Document                          | V5-00 Truth Lock | V5-01 Governance Resolution | V5-02 Security Blockers | V5-03 Capability Ownership | V5-04 Dogfooding Matrix | V5-05 Filesystem/Storage/Cache | V5-06 Metadata Compilation | V5-07 Modern PHP | V5-08 Attributes | V5-09 DI/Autowiring | V5-10 Data Structures | V5-13 Request/Superglobals | V5-17 Serialization | V5-18 Async/Concurrency | V5-19 Pooling | V5-20 Hot Path Cache | V5-21 Tooling Gates | V5-22 E2E Tests | V5-23 Final Truth |
|----------------------------------------------|:----------------:|:---------------------------:|:-----------------------:|:--------------------------:|:-----------------------:|:------------------------------:|:--------------------------:|:----------------:|:----------------:|:-------------------:|:---------------------:|:--------------------------:|:-------------------:|:-----------------------:|:-------------:|:--------------------:|:-------------------:|:---------------:|:-----------------:|
| how-to-architecture.md                       |     Partial      |            Pass             |         Partial         |            Pass            |          Pass           |              Pass              |            Pass            |       Pass       |       Pass       |        Pass         |         Pass          |            Pass            |        Pass         |          Pass           |     Pass      |         Pass         |        Pass         |      Pass       |       Pass        |
| how-to-architecture-extension-with-ddd.md    |        NA        |             NA              |           NA            |             NA             |           NA            |               NA               |             NA             |        NA        |        NA        |         NA          |          NA           |             NA             |         NA          |           NA            |      NA       |          NA          |         NA          |       NA        |        NA         |
| how-to-clean-code.md                         |        NA        |             NA              |          Pass           |            Pass            |          Pass           |              Pass              |            Pass            |       Pass       |       Pass       |        Pass         |         Pass          |            Pass            |        Pass         |          Pass           |     Pass      |         Pass         |        Pass         |      Pass       |       Pass        |
| how-to-code-review.md                        |        NA        |             NA              |          Pass           |            Pass            |          Pass           |              Pass              |            Pass            |       Pass       |       Pass       |        Pass         |         Pass          |            Pass            |        Pass         |          Pass           |     Pass      |         Pass         |        Pass         |      Pass       |       Pass        |
| how-to-code-style.md                         |        NA        |             NA              |          Pass           |            Pass            |          Pass           |              Pass              |            Pass            |       Pass       |       Pass       |        Pass         |         Pass          |            Pass            |        Pass         |          Pass           |     Pass      |         Pass         |        Pass         |      Pass       |       Pass        |
| how-to-coding-standards.md                   |        NA        |             NA              |          Pass           |            Pass            |          Pass           |              Pass              |            Pass            |       Pass       |       Pass       |        Pass         |         Pass          |            Pass            |        Pass         |          Pass           |     Pass      |         Pass         |        Pass         |      Pass       |       Pass        |
| how-to-design-components.md                  |        NA        |             NA              |          Pass           |            Pass            |          Pass           |              Pass              |            Pass            |       Pass       |       Pass       |        Pass         |         Pass          |            Pass            |        Pass         |          Pass           |     Pass      |         Pass         |        Pass         |       NA        |       Pass        |
| how-to-document.md                           |        NA        |            Pass             |          Pass           |            Pass            |          Pass           |              Pass              |            Pass            |       Pass       |       Pass       |        Pass         |         Pass          |            Pass            |        Pass         |          Pass           |     Pass      |         Pass         |        Pass         |      Pass       |       Pass        |
| how-to-dogfooding.md                         |        NA        |             NA              |           NA            |            Pass            |          Pass           |              Pass              |             NA             |        NA        |        NA        |         NA          |         Pass          |             NA             |         NA          |          Pass           |     Pass      |         Pass         |         NA          |       NA        |        NA         |
| how-to-modern-php-attributes-di.md           |        NA        |             NA              |           NA            |             NA             |           NA            |               NA               |            Pass            |       Pass       |       Pass       |        Pass         |         Pass          |            Pass            |        Pass         |           NA            |      NA       |         Pass         |        Pass         |       NA        |        NA         |
| how-to-production-readiness.md               |       Pass       |             NA              |          Pass           |             NA             |           NA            |               NA               |             NA             |        NA        |        NA        |         NA          |          NA           |             NA             |         NA          |           NA            |      NA       |          NA          |         NA          |       NA        |       Pass        |
| how-to-system-performance.md                 |        NA        |             NA              |           NA            |             NA             |           NA            |               NA               |             NA             |        NA        |        NA        |         NA          |          NA           |             NA             |         NA          |          Pass           |     Pass      |         Pass         |         NA          |       NA        |        NA         |
| how-to-system-security.md                    |        NA        |             NA              |          Pass           |             NA             |           NA            |               NA               |             NA             |        NA        |        NA        |         NA          |          NA           |            Pass            |        Pass         |           NA            |      NA       |          NA          |         NA          |      Pass       |        NA         |
| how-to-unit-test.md                          |        NA        |             NA              |          Pass           |             NA             |           NA            |               NA               |             NA             |        NA        |        NA        |         NA          |          NA           |             NA             |         NA          |           NA            |      NA       |          NA          |         NA          |      Pass       |        NA         |
| how-to-use-advanced-architecture-patterns.md |        NA        |             NA              |           NA            |             NA             |           NA            |               NA               |             NA             |        NA        |        NA        |         NA          |          NA           |             NA             |         NA          |           NA            |      NA       |          NA          |         NA          |       NA        |        NA         |

## Status Legend

- **Pass**: Rules from this document are checked and applied in this stage
- **Partial**: Some rules applied, others not yet relevant
- **NA**: Document does not apply to this specific stage (reason documented)
- **Fail**: Rule violated (none at this stage)
- **Blocked**: Cannot evaluate (none at this stage)

## NA Justifications

| Document                                     | Stage                             | Reason                                                                                                     |
|----------------------------------------------|-----------------------------------|------------------------------------------------------------------------------------------------------------|
| how-to-architecture-extension-with-ddd.md    | All early V5 stages               | No DDD domain modeling work in readiness pass                                                              |
| how-to-use-advanced-architecture-patterns.md | All early V5 stages               | No advanced pattern implementation in readiness pass                                                       |
| how-to-dogfooding.md                         | V5-00, V5-01, V5-02               | Truth lock, governance resolution, and initial security cleanup do not involve component adoption scanning |
| how-to-modern-php-attributes-di.md           | V5-00 through V5-04               | Readiness pass does not modify PHP syntax, attributes, or DI                                               |
| how-to-system-performance.md                 | V5-00 through V5-04               | Readiness pass does not touch hot paths or performance-sensitive code                                      |
| how-to-production-readiness.md               | V5-01, V5-03-V5-22 (except noted) | Governance resolution and intermediate adoption stages are not production-readiness gates                  |
| how-to-unit-test.md                          | Non-implementation stages         | No new tests written in truth lock, governance resolution stages                                           |

## Governance Compliance Summary

Total how-to documents: 15
Documents read: 15
Documents applicable to this readiness pass: 8
Documents not applicable (with reasons): 7

No how-to document was silently ignored.
