# HttpClient

Outbound HTTP is still part of Foundation HTTP, but it is not the same ownership axis as inbound request handling.

- inbound HTTP owns request assembly, middleware, router integration, and response creation
- outbound HTTP owns client transports, retries, aggregation, and error handling

Keeping these concerns separated avoids another mixed “HTTP utils” bucket.
