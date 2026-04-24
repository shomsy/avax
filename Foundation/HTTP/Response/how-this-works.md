# Response - how this works

`Response/*` owns response creation and low-level stream helpers.

- `ResponseFactory` is the primary public entry point for creating PSR-7 responses.
- `JsonResponse` is a convenience wrapper for structured JSON payloads.
- `Classes/*` contains local response/stream primitives that support response handling where a concrete PSR
  implementation is needed.
