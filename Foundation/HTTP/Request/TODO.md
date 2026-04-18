# Request Component Hardening TODO

## Completed

- [x] Backup created via merge-files.sh
- [x] Thin RequestInit: Refactored with promoted properties and copy() method
- [x] Harden PrepareRequest body handling: Stage-based assembly with single IO capture
- [x] Lock RequestBody::content() contract
- [x] Expand RequestedInputs tests (Composition over inheritance model)
- [x] Review/add tests: Headers, Cookies, Attributes, Session semantics
- [x] Network trust abuse tests (X-Forwarded-For spoofing protection)
- [x] PSR characterization + assembly regression tests
- [x] Create docs/ADRs: request-architecture-language.md, psr-surface-vs-internal-ownership.md
- [x] Canonical root restoration (ServerRequest\IncomingRequest is the only truth)
- [x] ServiceProvider aligned with AssembleIncomingRequest flow
- [x] Legacy PublicEntryPointRequest removed
