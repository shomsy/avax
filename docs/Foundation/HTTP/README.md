# Foundation HTTP

This mirror documents the live HTTP component shape after the outer-runtime cleanup.

- Router is treated as its own subsystem.
- Request assembly, middleware orchestration, response creation, session ownership, and URI helpers remain under the
  Foundation HTTP umbrella.
- Legacy component-local docs/tests that described removed architectures were intentionally deleted in favor of this
  mirror and `how-this-works.md` files next to the source.
