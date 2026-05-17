# Canonical Terms Gate

## Implementation

`tooling/governance/check-canonical-terms.php`

Verifies registry at `docs/governance/canonical-terms.md` exists and contains required columns and critical terms.

## Result

PASS — all required columns (canonical term, meaning, allowed aliases, forbidden aliases) and critical terms (Response, CreateHttpResponse, ServiceProvider, Runtime, EventEmitter, FailureBoundary) present.
