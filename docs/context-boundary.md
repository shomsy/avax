<!-- SPDX-License-Identifier: MIT -->

# Context Boundaries for HTTP Globals

## Decision

All incoming HTTP metadata (headers, cookies, request URI, session metadata, etc.) flows through a *single read-only
ship*: the `HttpContextInterface` service and its friends. These services are the **only** consumers of PHP
superglobals. Every other part of the Foundation must ask one of these services for host/scheme/IP/session/cookie data.

## Why?

1. **One canonical source of truth** for HTTP data makes security policies and helpers consistent.
2. **No random superglobals** in random services means easier testing, CLI/worker headless mode, and predictable
   behavior.
3. **Documentation and enforcement** keep future contributors honest: the rule is not “fix superglobal leaks as you see
   them”, it is “never introduce new ones outside the boundary”.

## Enforcement

- `HttpContext` reads globals via `PhpGlobalsProvider`.
- `SessionContext` combines HTTP data with the session store to keep the security layer read-only.
- The `scripts/check-superglobals.php` command scans `Foundation/` for any remaining `$_SERVER`, `$_GET`, `$_POST`,
  `$_COOKIE`, `$_FILES`, `$_SESSION`, or `$_REQUEST` usage outside the known boundary files.
- Run `composer check:superglobals` to validate before merging. Add it to CI if you want automated protection.

## Next steps

1. When new HTTP helpers or session policies are added, inject `HttpContextInterface`/`SessionContextInterface`.
2. Keep the allowed files list in `scripts/check-superglobals.php` up to date.
3. If you find legitimate cases that need superglobals (e.g., a third-party dependency), extend the contexts rather than
   referencing them directly.
