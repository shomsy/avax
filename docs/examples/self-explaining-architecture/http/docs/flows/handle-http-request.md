# Flow: Handle HTTP Request

## Description

This flow describes the complete lifecycle of handling a single HTTP request from arrival to response.

## Steps

1. **Request Arrives**
   - HTTP request arrives at the PublicSurface entry point
   - Request object is created from raw HTTP data

2. **Route Matching**
   - Router looks up the URL in the compiled route table
   - If no match: return 404 immediately
   - If match: identify the target Flow class

3. **Middleware Pipeline**
   - Authentication middleware: verify identity (if required)
   - Authorization middleware: verify permissions (if required)
   - Rate limiting middleware: check request rate
   - Input validation middleware: validate Content-Type, body schema
   - If any middleware rejects: return error immediately (401/403/429/400)

4. **Flow Execution**
   - Flow receives validated request and response objects
   - Flow executes business behavior (orchestrates capabilities)
   - Flow writes result to response object

5. **Response Building**
   - Response object formats the result (JSON, HTML, file download, etc.)
   - Response headers are set (Content-Type, Cache-Control, etc.)

6. **Response Sent**
   - Response is sent to the client
   - Request lifecycle event is emitted
   - Request-scoped state is reset (for long-lived workers)

## Negative Paths

| What Goes Wrong | Response | Where It's Handled |
|----------------|----------|-------------------|
| No route matches | 404 Not Found | Router |
| Authentication fails | 401 Unauthorized | Auth middleware |
| Authorization fails | 403 Forbidden | Authz middleware |
| Rate limit exceeded | 429 Too Many Requests | Rate limit middleware |
| Invalid input | 400 Bad Request | Validation middleware |
| Flow throws exception | 500 Internal Server Error | Error handler |
| Response build fails | 500 Internal Server Error | Error handler |
| Worker timeout | 503 Service Unavailable | Runtime supervisor |
