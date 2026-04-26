# Risk Register

Tracks active and accepted risks.

## Entry Format

- `id`:
- `identified_at`:
- `updated_at`:
- `severity`: low | medium | high | critical
- `likelihood`: low | medium | high
- `impact`:
- `mitigation`:
- `owner`:
- `status`: open | accepted | mitigated | closed

## Active Risks

- `id`: `RISK-001`
  `identified_at`: `2026-04-08 17:04 CEST`
  `updated_at`: `2026-04-08 18:16 CEST`
  `severity`: `medium`
  `likelihood`: `medium`
  `impact`:
  `The previous hold-level risk was that advanced lifetimes, conditional composition, and deeper anti-pattern resistance were only partially implemented.`
  `mitigation`:
  `Closed by finishing TODO-016 with runtime, validation, docs, and smoke evidence for advanced lifetimes, conditionals, policy findings, test composition helpers, and story-grade errors.`
  `owner`: `container component`
  `status`: `closed`
