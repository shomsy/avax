---
title: RequestedInputs-how-this-works
owner: HTTP Foundation Team
last_reviewed: 2026-04-17
classification: internal
---

# RequestedInputs How This Works

## What this folder is

This folder contains the **input accessor layer** for HTTP requests. It is the single entry point for accessing all HTTP
request inputs (query parameters, parsed body, uploaded files) with typed accessors, security sanitization, and DTO
mapping.

This is NOT about:

- Request routing
- Response formatting
- Authentication/authorization
- Validation (that's handled by DTOs)

## Real commands or triggers that reach this folder

- `ServerRequest::inputs()` - Creates RequestedInputs from HTTP request
- `$request->inputs()` - In controllers to access input data

## Exact upstream handoffs

- `Foundation/HTTP/Request/ServerRequest.php`
    - function: `ServerRequest::inputs()`
    - Creates: `new RequestedInputs(queryParams, parsedBody)`

## The simplest story

1. HTTP request arrives at `ServerRequest`
2. Body is parsed (JSON, form, etc.)
3. `$request->inputs()` creates `RequestedInputs`
4. Controller accesses typed values: `$inputs->string('email')`
5. Outputs to HTML? Use: `$inputs->sanitizedHtml('content')`

## The first important path

```mermaid
sequenceDiagram
    participant HTTP as HTTP Request
    participant SR as ServerRequest
    participant RI as RequestedInputs
    participant Ctrl as Controller
    HTTP ->> SR: Incoming HTTP request
    SR ->> SR: Parse body (JSON/form)
    SR ->> RI: new RequestedInputs(query, body)
    RI ->> Ctrl: $request->inputs()
    Ctrl ->> RI: $inputs->string('email')
    RI -->> Ctrl: "user@example.com"
    Ctrl ->> RI: $inputs->sanitizedHtml('content')
    RI -->> Ctrl: "&lt;script&gt;...&lt;/script&gt;" (safe)
```

- **Step 1:** ServerRequest parses incoming HTTP request
- **Step 2:** RequestedInputs created with query + body data
- **Step 3:** Controller gets typed values
- **Step 4:** For HTML output, use sanitized methods

## Direct files in this folder

### RequestedInputs.php

The main facade - extends Inputs and adds:

- Security sanitization (SanitizesInput trait)
- DTO mapping via `as()` method
- Typed accessors via AccessesTypedValues trait

Key methods:

- `string()`, `int()`, `bool()`, `float()`, `enum()` - typed values
- `sanitizedHtml()`, `sanitizedJs()`, `sanitizedPath()` - security
- `as(MyDTO::class)` - map to DTO/Command

### Inputs.php

Base Value Object combining query + body:

- Body takes precedence over query on conflicts
- Provides: `query()`, `body()`, `all()`, `get()`, `has()`

### AccessesTypedValues.php

Shared trait with typed accessor methods:

- Eliminates code duplication
- Used by Inputs, QueryParams, ParsedBody

### SanitizesInput.php

OWASP-compliant security traits:

- HTML encoding (XSS prevention)
- JavaScript escaping
- Path sanitization
- Regex escaping
- Control character removal
- UTF-8 normalization

### QueryParams.php

Query string only - Value Object for /?foo=bar style inputs.

### ParsedBody.php

Parsed body only - Value Object for POST body data.

### InputValue.php

Single input value wrapper with type conversion.

## Security boundaries (OWASP)

```
┌─────────────────────────────────────────┐
│         HTTP Request (UNTRUSTED)          │
└──────────────────┬──────────┬─────────────┘
                   │
                   ▼
┌─────────────────────────────────────────┐
│         RequestedInputs                   │
│  ┌───────────────────────────────┐     │
│  │ Input enters here           │     │
│  │ - raw, unsanitized        │     │
│  │ - potentially malicious  │     │
│  └───────────────────────────────┘     │
└──────────────────┬──────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
        ▼                     ▼
   Typed access          Security sanitization
   (for logic)          (for output)
   string()            sanitizedHtml() -> HTML
   int()               sanitizedJs() -> JavaScript
   bool()             sanitizedPath() -> filesystem
```

Security implementation:

- **For logic**: Use `string()`, `int()`, etc.
- **For HTML output**: Use `sanitizedHtml()`
- **For JS output**: Use `sanitizedJs()`
- **For filesystem**: Use `sanitizedPath()`

## What to remember

1. **Input enters untrusted** - Always sanitize before output
2. **Body > query** - On conflicts, body wins
3. **Use typed accessors** - `string()` not `get()` for type safety
4. **Use DTOs for validation** - `$inputs->as(MyDTO::class)`
5. **Security is layered** - Logic uses typed methods, output uses sanitized*

## Debug first

- Start in `RequestedInputs.php` when inputs seem wrong
- Start in `SanitizesInput.php` when XSS warnings appear
- Start in `AccessesTypedValues.php` when typed accessors fail
