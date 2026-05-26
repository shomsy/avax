# Request Pipeline

> Shows how an HTTP request flows through the middleware pipeline to the handler and back.

## Diagram

```mermaid
sequenceDiagram
    autonumber
    participant Client as HTTP Client
    participant Router as Router
    participant Pipeline as MiddlewarePipeline
    participant Auth as AuthMiddleware
    participant Log as LoggingMiddleware
    participant Handler as RequestHandler
    participant Response as ResponseEmitter

    Client->>Router: HTTP Request
    Router->>Pipeline: dispatch with middleware stack
    Pipeline->>Auth: middleware[0]: process(request)
    alt Request Rejected
        Auth-->>Response: error response
    else Request Approved
        Auth-->>Pipeline: modified request + context
        Pipeline->>Log: middleware[1]: process(request)
        Log-->>Pipeline: request (logged)
        Pipeline->>Handler: dispatch to handler
        Handler-->>Pipeline: response
        Pipeline->>Log: middleware[1]: process(response)
        Log-->>Pipeline: response (logged)
        Pipeline->>Auth: middleware[0]: process(response)
        Auth-->>Pipeline: response
        Pipeline->>Response: emit
        Response-->>Client: HTTP Response
    end
```

## Step-by-Step

1. **Request arrives:** Router receives HTTP request and identifies matching route and middleware stack
2. **Pipeline starts:** Pipeline iterates through middleware in registration order
3. **Auth middleware:** AuthMiddleware extracts token, validates it, attaches identity context. Can reject with 401
4. **Logging middleware:** LoggingMiddleware logs the incoming request
5. **Handler processes:** Handler executes domain logic and returns response
6. **Response travels back:** Response passes through middleware in reverse order (logging then auth)
7. **Response emitted:** ResponseEmitter sends the HTTP response to client

## Participants

| Participant | Role |
|-------------|------|
| Router | Route matching, middleware stack selection |
| MiddlewarePipeline | Ordered middleware execution |
| AuthMiddleware | Authentication check |
| LoggingMiddleware | Request/response logging |
| RequestHandler | Domain logic execution |
| ResponseEmitter | HTTP response emission |

## Related

- Dictionary: Middleware Pipeline
- ADR: 0001 - Pipeline ordering policy
