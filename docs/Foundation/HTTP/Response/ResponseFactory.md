# ResponseFactory

`ResponseFactory` is the PSR-17 style entrypoint plus the temporary compatibility facade for older `create*Response()`
and `response()/send()` call sites.

Its job is narrow:

- create empty responses
- expose compatibility helpers while callers migrate to `Response::*`
- avoid owning rendering or business JSON envelope conventions
