# HttpClient - how this works

`HttpClient/*` is the outbound HTTP subsystem.

- It currently uses its own request/response object model and transport-specific clients.
- This refactor pass did not force it into the same PSR-7 surface used by inbound HTTP because that would be a separate migration with higher blast radius.
- The important rule for now is ownership clarity: transport clients, retry policies, and outbound request traits stay inside `HttpClient/*` and do not leak into inbound HTTP runtime code.
