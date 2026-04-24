# Session Docs

This folder is retained only as a narrow compatibility doc mirror for the active Session ownership tree.

- The live public surface is `Session.php`, `SessionInterface.php`, `SessionScope.php`, and `NullSession.php`.
- Flow owners live under folders such as `ReadSessionValue`, `WriteSessionValue`, `DeleteSessionValue`,
  `RememberSessionValue`, `RegenerateSessionId`, and `TerminateSession`.
- Capability owners live under `SessionStore`, `SessionRecovery`, `SessionSecurity`, `SessionCookie`, and
  `SessionRegistry`.
- Backwards-compatible buckets `Audit`, `Events`, and `Recovery` remain only for existing callers that still target
  those names.

Authoritative architectural summaries for this refactor pass live in:

- `Foundation/HTTP/Session/how-this-works.md`
- `Foundation/HTTP/how-this-works.md`
- `docs/Foundation/HTTP/Concepts/Session.md`
