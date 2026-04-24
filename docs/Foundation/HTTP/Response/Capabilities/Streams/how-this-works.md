# Streams

The streams capability owns concrete stream creation.

- `ResponseStreamFactory` creates empty streams, wraps existing resources, and opens files
- `BuildFileDownloadResponse` depends on this capability for readable file bodies
- `EmitResponseBody` depends on `RewindStream` so emitted content starts from the correct position
