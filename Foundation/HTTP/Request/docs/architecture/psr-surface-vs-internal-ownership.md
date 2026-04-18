# PSR Surface vs. Internal Ownership

This document defines the boundary between the public PSR-7 interface and the internal specialized architecture of the Request component.

## The Boundary Principle

The `Request` class acts as a **Thin Interface Wrapper** over a **Specialized State Model**.

- **Public Surface**: Strictly adheres to `Psr\Http\Message\ServerRequestInterface`.
- **Internal Ownership**: Delegates logic to specialized capability owners.

## Responsibility Mapping

| Feature | PSR-7 Surface | Internal Owner |
| :--- | :--- | :--- |
| **Headers** | `getHeader()`, `withHeader()` | `RequestHeaders` |
| **Body** | `getBody()`, `getParsedBody()` | `RequestBody` / `ParsedBody` |
| **Inputs** | `queryParams`, `parsedBody` | `RequestedInputs` |
| **URI** | `getUri()`, `withUri()` | `UriInterface` / `UriBuilder` |
| **Target** | `getRequestTarget()` | `ReadRequestTarget` |
| **Trust** | N/A (Internal only) | `ResolveClientAddress` |

## Assembly Flow

1. **Capture**: `PrepareRequest` captures raw environment data.
2. **Normalize**: Action owners standardize parts (Headers, Protocol, Files).
3. **Initialize**: `RequestInit` holds the normalized parts.
4. **Wrap**: `Request` is instantiated with the completed `ServerInit` context.

## Immutability Chain

When a PSR-7 `with*()` method is called:
1. `Request` delegates the change to `RequestInit`.
2. `RequestInit` returns a new instance of itself.
3. `Request` returns a new instance of itself with the updated `RequestInit`.
