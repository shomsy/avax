# HTTP Component

## What This Component Owns

The HTTP component owns everything related to HTTP request handling, response building, middleware pipelines, and routing.

It receives HTTP requests from the outside world and turns them into AvaX Flow executions.

It does NOT own business logic. It owns the HTTP boundary.

## Where It Belongs

```
components/HTTP/
  System/
    PublicSurface/     # Entry points: Request, Response, Middleware pipeline
    Flows/             # HTTP-specific flows: routing, middleware execution, dispatch
    Capabilities/      # Reusable HTTP behavior: request parsing, response formatting
    Configuration/     # Route registration, middleware assembly
    Foundation/        # Tiny HTTP primitives
```

## How It Works (The Simple Version)

1. A request comes in from the internet
2. The PublicSurface catches it and says "I got this"
3. It asks the Router "which Flow handles this URL?"
4. The Router says "This one!"
5. The Middleware Pipeline checks "is this request allowed?" (auth, rate limit, etc.)
6. The Flow runs the actual behavior
7. The Response builder turns the result into HTML/JSON/whatever the client wants
8. The client gets their answer

## What It Does NOT Own

- Business logic (that belongs in component Flows)
- Database queries (that belongs in DataStack or persistence capabilities)
- Authentication decisions (that belongs in Identity/Security)
- File system operations (that belongs in Filesystem capability)

## Dependencies

- Uses AvaX Runtime for lifecycle management
- Uses AvaX Events for request lifecycle events
- Uses AvaX Logger for request logging
- Uses AvaX Security for authorization checks

## How It Fails

- Invalid routes return 404 (fail closed)
- Middleware errors return 500 (fail closed, never expose internals)
- Request parsing errors return 400 (fail closed)
- Timeout errors return 503 (fail closed)

## How It Is Tested

- Unit tests for request/response parsing
- Integration tests for middleware pipelines
- Contract tests for public HTTP APIs
- Negative tests for invalid requests, malformed headers, injection attempts
