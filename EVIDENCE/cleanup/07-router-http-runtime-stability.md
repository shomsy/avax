# Stage F Router, HTTP, and Runtime Entry Stability

Date: 2026-05-14
Status: GREEN

## Checked

- 566 HTTP/Router tests pass (4986 assertions)
- 133 Router-specific tests pass (3981 assertions, nonzero confirmed)
- Router anonymous group class extracted to named `Capabilities/RouteGroup/RouteGroupRegistrar.php`
- Router/Dispatcher verified stateless (no request stored as instance state)
- Localhost/127.0.0.1 references classified: all are dev tooling or documented dev defaults
- No production runtime localhost exposure found
- Full test suite: 8293/8293 pass

## Changes

- `components/HTTP/Router/System/PublicSurface/Router.php`: Anonymous class in `group()` extracted to `Capabilities/RouteGroup/RouteGroupRegistrar.php`
- `components/HTTP/Router/System/Capabilities/RouteGroup/RouteGroupRegistrar.php`: New named capability class

## Remaining

- Router still has development default base URI (`http://localhost`); production must override through configuration (acceptable dev default pattern)
- Known intermittent test isolation flakiness in V4SecurityPolicyTest (pre-existing, not caused by Phase F changes)

Ledger: SW-0017 — FIXED_NOW
