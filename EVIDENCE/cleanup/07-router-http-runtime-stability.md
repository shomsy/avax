# Stage F Router, HTTP, and Runtime Entry Stability

Date: 2026-05-13
Status: YELLOW_PARTIAL

## Checked

- Full PHPUnit passed, including existing Router/HTTP/runtime tests in the central suite.
- PHPStan passed after Router and ControllerResolver fixes.
- PublicSurface and runtime leak gates passed.

## Remaining

- Full Router/HTTP focused test report was not split out separately.
- Runtime health/doctor story for Router is missing and blocks V5.9.
- Current `Router` still has a development default base URI; explicit configuration policy needs Stage F/H review.

Ledger: SW-0017.
