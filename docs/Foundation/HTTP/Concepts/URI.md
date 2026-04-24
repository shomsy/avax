# URI

URI helpers stay lightweight.

- `UriBuilder` composes immutable URI state.
- `Components/*` validate scheme/host/path pieces.
- `QueryParams` handles query mutation without leaking URI parsing concerns into unrelated HTTP runtime code.
