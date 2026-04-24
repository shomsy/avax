# EmitResponse

`EmitResponse` owns runtime output.

- `ResponseEmitter` hands a finished `ResponseInterface` to this flow.
- `EmitResponseStatus` sends the status code.
- `EmitResponseHeaders` sends headers.
- `EmitResponseBody` rewinds the stream when possible and writes body bytes out.
