# Session - how this works

`Session/*` is split into three layers:

1. Public surface: `Session.php`, `SessionInterface.php`, `SessionScope.php`, `NullSession.php`.
2. Flow owners: `ReadSessionValue`, `WriteSessionValue`, `DeleteSessionValue`, `RememberSessionValue`,
   `RegenerateSessionId`, `TerminateSession`, snapshot/import/export flows, and actor/transaction helpers.
3. Capability owners: `SessionStore`, `SessionRecovery`, `SessionSecurity`, `SessionCookie`, `SessionRegistry`, plus BC
   buckets `Audit`, `Events`, and `Recovery`.

This pass removed dead archives and stale docs, kept backwards-compatible bucket classes only where callers still rely
on them, and normalized the live file layout so Composer can discover the component correctly.
