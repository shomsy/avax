# Failure Semantics

## Failure Points

- Missing dispatcher dependency: now fails at `App` construction/assembly time.
- Controller resolution failure: unchanged, still handled by existing `RunApplication` resolver path.
- Route not found / method not allowed: unchanged.
- Custom exception handler failure: unchanged, `App` falls through to default handler.

## Message Safety

No new error messages are introduced.

Existing production behavior still returns `Internal server error` for unhandled exceptions.

## Failure Classification

- missing object graph dependency: FATAL configuration/assembly failure
- route not found: FAIL_CLOSED 404 response
- method not allowed: FAIL_CLOSED 405 response
- unhandled dispatch failure: fail through existing exception behavior

## Worker State After Failure

`App::handle()` still closes request scope in `finally`.

## Decision

Failure semantics are preserved and slightly improved because missing dispatcher dependency cannot be hidden until the first request.
