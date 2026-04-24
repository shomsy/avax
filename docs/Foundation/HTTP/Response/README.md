# HTTP Response

`Foundation/HTTP/Response` is the canonical response component for this repository.

It provides:

- a small public facade through `Response`
- a PSR-17 style compatibility entry through `ResponseFactory`
- runtime emission through `ResponseEmitter`
- explicit build flows for text, HTML, JSON, XML, problem-details, redirects, downloads, streams, 204, and 304 responses
- shared capabilities for headers, body encoding, streams, cookies, redirects, downloads, and caching

Legacy `Classes/*`, `JsonResponse`, and the old XML helper were removed as part of the full migration.
