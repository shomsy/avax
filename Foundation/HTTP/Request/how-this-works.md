# Request - how this works

`Request/*` owns request assembly and typed input access.

- `RequestServiceProvider` wires request assembly services into the container.
- `ServerRequest/IncomingRequest/*` is the real assembly pipeline: protocol normalization, headers, body parsing,
  uploaded files, trusted proxy handling, and typed requested inputs.
- `Request.php` is the DTO-facing public surface for typed request classes built from an attached `ServerRequest`.

Legacy component-local test trees were removed. The active test surface lives under `tests/Foundation/HTTP/Request`.
