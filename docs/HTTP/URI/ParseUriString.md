# ParseUriString

Boundary parser for external URI strings.

## Responsibilities

- Parse raw URI strings using parse_url
- Create typed part objects
- Validate overall structure
- Single entry point for parsing

## Security

- Trust boundary for untrusted input
- Fails fast on malformed URIs
- No re-parsing of internal state