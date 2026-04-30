# HTTP Suite Normalization - Truth Inventory

## Phase 1: Truth Report

### Current State Summary

| Area       | HTTP/ (canonical) | Top-level | Status    |
|------------|-------------------|-----------|-----------|
| Session    | 197 files         | 29 files  | DUPLICATE |
| Middleware | 20 files          | 5 files   | DUPLICATE |
| Request    | in HTTP           | 13 files  | DUPLICATE |
| Response   | in HTTP           | 10 files  | DUPLICATE |
| Router     | in HTTP           | 55 files  | DUPLICATE |

### Analysis

#### Session

- **canonical owner**: `components/HTTP/Session` (197 files)
- **duplicate**: `components/Session` (29 files)
- **Decision**: HTTP/Session wins - contains full session system with events, recovery, stores

#### Middleware

- **canonical owner**: `components/HTTP/Middleware` (20 files)
- **duplicate**: `components/Middleware` (5 files)
- **Decision**: HTTP/Middleware wins

#### Request

- **canonical owner**: `components/HTTP/Request` (rich structure)
- **duplicate**: `components/Request` (13 files)
- **Decision**: HTTP/Request wins OR bridge

#### Response

- **canonical owner**: `components/HTTP/Response` (rich structure)
- **duplicate**: `components/Response` (10 files)
- **Decision**: HTTP/Response wins OR bridge

#### Router

- **canonical owner**: `components/HTTP/Router` (rich structure)
- **duplicate**: `components/Router` (55 files)
- **Decision**: HTTP/Router wins OR bridge, but router may stay under ADR discussion

### Next Actions

| Path                       | Current Role    | Final Owner     | Action           |
|----------------------------|-----------------|-----------------|------------------|
| components/HTTP/Session    | canonical owner | HTTP/Session    | KEEP             |
| components/Session         | duplicate       | HTTP/Session    | BRIDGE or DELETE |
| components/HTTP/Middleware | canonical owner | HTTP/Middleware | KEEP             |
| components/Middleware      | duplicate       | HTTP/Middleware | BRIDGE or DELETE |
| components/HTTP/Request    | canonical owner | HTTP/Request    | KEEP             |
| components/Request         | duplicate       | HTTP/Request    | BRIDGE or DELETE |
| components/HTTP/Response   | canonical owner | HTTP/Response   | KEEP             |
| components/Response        | duplicate       | HTTP/Response   | BRIDGE or DELETE |
| components/HTTP/Router     | canonical owner | HTTP/Router     | KEEP             |
| components/Router          | duplicate       | HTTP/Router     | Pending ADR      |

---

*Generated: Phase 1 Truth Inventory*
*Date: 2026-04-28*