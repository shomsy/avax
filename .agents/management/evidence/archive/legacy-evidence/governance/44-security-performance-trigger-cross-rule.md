# Security and Performance Trigger Cross-Rule

## Decision

Added to:
- `how-to-system-security.md` — Section 44
- `how-to-system-performance.md` — Section 45
- `how-to-code-review.md` — Section 20
- `how-to-production-readiness.md` — Section 22

Requires security review for changes touching authentication, authorization, sessions, encryption, filesystem I/O, serialization, etc. Requires performance review for changes touching hot paths, loops, reflection, container resolution, route matching, etc.
