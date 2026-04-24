# BuildResponse

`BuildResponse` owns response creation.

- `Response` facade calls hand off to explicit builders such as `BuildTextResponse`, `BuildJsonResponse`, and
  `BuildRedirectResponse`.
- Each builder encodes only one response shape and then delegates final assembly to `BuildResponse`.
- `BuildResponse` produces an immutable `ResponseMessage`.
