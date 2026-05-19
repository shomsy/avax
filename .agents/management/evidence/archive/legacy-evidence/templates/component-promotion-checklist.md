# Component Promotion Checklist

Component: __NAME__

Plane: __PLANE__

Status: Draft / Experimental / Internal / Partial / Platform-Ready

## Shape

- [ ] canonical System shape
- [ ] no forbidden folders
- [ ] PublicSurface justified
- [ ] Flows justified
- [ ] Capabilities present
- [ ] Configuration justified
- [ ] Foundation minimal

## Behavior

- [ ] public API proven
- [ ] internal behavior proven
- [ ] failure model proven
- [ ] runtime safety proven

## Security

- [ ] boundary identified
- [ ] validation present
- [ ] authorization present where needed
- [ ] secrets redacted
- [ ] state does not leak

## Performance

- [ ] hot path identified
- [ ] hidden I/O absent
- [ ] bounded work
- [ ] performance claims have evidence

## Tests

- [ ] unit tests
- [ ] integration tests where needed
- [ ] compatibility tests where exported
- [ ] failure tests

## Docs

- [ ] component README summary
- [ ] docs canonical page
- [ ] examples
- [ ] operator diagnostics

## Decision

Promote / Keep Internal / Reject / Needs Repair