# Stage Report: Normative Consistency Audit

## 1. Audit Overview

Every how-to document is audited for governance keyword consistency.

Keywords: MUST, MUST NOT, REQUIRED, MANDATORY, SHOULD, SHOULD NOT, MAY, FORBIDDEN, BLOCKER, HIGH, MEDIUM, LOW, GREEN,
YELLOW, RED.

## 2. Audit Findings

| File                                                    | Rule area                            | Problem                                     | Current wording                                         | Correct wording                                              | Severity |
|---------------------------------------------------------|--------------------------------------|---------------------------------------------|---------------------------------------------------------|--------------------------------------------------------------|----------|
| how-to-dependency-injection.md                          | 3.6 Optional Dependency Binding Rule | Weak wording for a rule                     | "Rules: Register explicit default bindings first."      | "MUST register explicit default bindings first."             | LOW      |
| how-to-dependency-injection.md                          | 3.6 Optional Dependency Binding Rule | Weak wording for a rule                     | "- Resolve dependencies from the container after that." | "- MUST resolve dependencies from the container after that." | LOW      |
| how-to-git.md                                           | Whole file                           | Informational style, needs normative weight | Varies                                                  | Use MUST/MUST NOT for workflow steps.                        | MEDIUM   |
| how-to-architecture.md                                  | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-runtime-composition.md                           | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-design-components.md                             | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-clean-code.md                                    | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-code-review.md                                   | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-production-readiness.md                          | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-coding-standards.md                              | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-architecture-extension-with-ddd.md               | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-code-style.md                                    | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-document.md                                      | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-dogfooding.md                                    | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-events-listeners-event-sourcing-cqrs-realtime.md | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-modern-php-attributes-di.md                      | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-system-performance.md                            | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-system-security.md                               | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-unit-test.md                                     | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |
| how-to-use-advanced-architecture-patterns.md            | -                                    | Audited, consistent                         | -                                                       | -                                                            | -        |

## 3. Patching Plan

- [x] how-to-dependency-injection.md: Patch Section 3.6. (Planned next)
- [x] how-to-git.md: Hardening normative language. (Planned next)
- [x] Final sweep of remaining how-to files: Audited and consistent.

## 4. Conclusion

Governance set is largely consistent. Minor hardening required in DI and Git documents to ensure absolute
enforceability.
Proceeding to execution.
