# URI - how this works

`URI/*` owns URI value composition and query parameter manipulation.

- `UriBuilder` is the public URI construction surface.
- `Components/*` validates individual URI parts.
- `Traits/*` contains PSR-7-style immutable helper behavior.
- `QueryParams` is the mutable query-string builder used by `UriBuilder`.
