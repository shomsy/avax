# Request Architecture Language

This document defines the canonical ownership language and naming conventions for the `Foundation/HTTP/Request` component.

## Ownership Principles

1. **Screaming Responsibility**: Each class name must declare its primary action or state responsibility.
2. **Immutable State**: State owners must never perform assembly or normalization.
3. **Action-Oriented Pipelines**: Assembly logic must be decomposed into explicit action owners.

## Component Vocabulary

### Flow Owners
| Name | Responsibility |
| :--- | :--- |
| `IncomingHttp` | Top-level namespace for the incoming HTTP boundary. |
| `IncomingRequest` | Core namespace for the primary request logic. |

### Entrypoint & Configuration
| Name | Responsibility |
| :--- | :--- |
| `AssembleIncomingRequest` | Single source of truth for request construction flows. |

### State Owners (Immutable)
| Name | Responsibility |
| :--- | :--- |
| `Request` | PSR-7 compliant state owner (ServerRequestInterface). |
| `RequestInit` | Temporary state container used during assembly. |
| `RequestedInputs` | Domain-aware view of request inputs (Query + Body). |
| `RequestBody` | Wrapper for the PSR-7 stream. |
| `RequestHeaders` | Case-insensitive header collection. |
| `RequestCookies` | Cookie collection. |
| `RequestAttributes` | Internal request attributes. |
| `UploadedFiles` | Tree of uploaded file objects. |

### Action Owners (Stateless)
| Name | Responsibility |
| :--- | :--- |
| `PrepareRequest` | Orchestrates the assembly pipeline. |
| `NormalizeHeaders` | Standardizes header keys and values. |
| `ReadRequestTarget` | Resolves the request-target from URI/explicit input. |
| `NormalizeProtocolVersion` | Validates and standardizes HTTP protocol string. |
| `ParseBodyByContentType` | Delegates body parsing based on Content-Type. |
| `NormalizeUploadedFiles` | Converts raw $_FILES to UploadedFile objects. |
| `ResolveClientAddress` | Resolves client IP through trusted proxy chain. |

## Forbidden Vocabulary

- Avoid technical suffix drift: `Helper`, `Manager`, `Bag`, `Base`.
- Avoid decorative naming: `AbsoluteServerRequest`.
- Avoid central data containers: No generic `ParameterBag` outside specialized collectors.
