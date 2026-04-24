# Session

Session now exposes a small public surface with flow/capability internals behind it.

- Flow owners model what Session does.
- Capability owners model what Session relies on.
- BC buckets remain only where existing callers still target them.

Dead archives, duplicate docs, and component-local test trees were removed because they described an obsolete Session
architecture.
