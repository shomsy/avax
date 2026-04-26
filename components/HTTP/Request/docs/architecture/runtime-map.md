---
title: Request Component Runtime Map
owner: HTTP Foundation Team
last_reviewed: 2026-04-18
classification: internal
---

# Request Component — Runtime Map

## Entrypoint

`AssembleIncomingRequest` — the only public factory for `ServerRequest`.

## Assembly Flow

```
AssembleIncomingRequest::fromGlobals()
  → PrepareRequest::fromGlobals()
    → captureRawBody()          (single IO read)
    → stageReadMethod()
    → stageReadProtocol()
    → stageReadUri()
    → stageExtractHeaders()
    → stageResolveBodyStream()  (from captured raw body)
    → stageParseBody()          (from captured raw body)
    → stageResolveSession()
    → RequestInit::fromResolvedParts()
  → ServerInit (bundles state + services)
  → ServerRequest (thin PSR-7 surface)
```

## State Owner

`RequestInit` — immutable container for all request state. Mutations via `copy()`.

## Capability Owners

| Capability     | Class                              |
|----------------|------------------------------------|
| Headers        | `RequestHeaders`                   |
| Body stream    | `RequestBody`                      |
| Parsed body    | `ParsedBody`                       |
| Cookies        | `RequestCookies`                   |
| Attributes     | `RequestAttributes`                |
| Session        | `RequestSession`                   |
| Uploaded files | `UploadedFiles`                    |
| Typed inputs   | `RequestedInputs` → `MergedInputs` |
| Request target | `ReadRequestTarget`                |

## Action Owners

| Action                 | Class                      |
|------------------------|----------------------------|
| Assembly pipeline      | `PrepareRequest`           |
| Header normalization   | `NormalizeHeaders`         |
| Protocol normalization | `NormalizeProtocolVersion` |
| File normalization     | `NormalizeUploadedFiles`   |
| Body parsing           | `ParseBodyByContentType`   |
| Input sanitization     | `InputSanitizer`           |
| DTO mapping            | `MapRequestedInputsToDto`  |

## Network Boundary (external to request state)

| Concern              | Class                     |
|----------------------|---------------------------|
| Proxy trust policy   | `TrustedProxyPolicy`      |
| Forwarded IP parsing | `ParseForwardedAddresses` |
| Client IP resolution | `ResolveClientAddress`    |

## DTO Boundary

`Avax\HTTP\Request\Request` — abstract base for request-backed DTOs.
Uses `ServerRequest::inputs()->all()` for hydration.
