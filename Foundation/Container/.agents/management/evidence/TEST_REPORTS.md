# Test Reports

Concrete records of executed verification.

## Entry Template

- `executed_at`:
- `scope`:
- `environment`:
- `checks`:
- `result`: pass | fail | partial
- `notes`:

## Reports

- `executed_at`: `2026-04-08 18:16 CEST`
  `scope`: `ownership-aware composition closure`
  `environment`: `docker php:8.3-cli`
  `checks`: `canonical php lint`, `tests/run-smoke-tests.sh`, `tests/check-diagnostics-contracts.sh`, `tests/check-benchmarks.sh`, `tests/check-peer-benchmarks.sh`, `git diff --check`
  `result`: `pass`
  `notes`: `Advanced lifetimes, disposal semantics, conditional composition, policy findings, structure diff, test composition helpers, and story-grade error paths all passed local verification. Peer benchmark gate reported no configured peer targets, which is the documented non-applicable path.`
- `executed_at`: `2026-04-08 17:04 CEST`
  `scope`: `ownership-aware composition foundations`
  `environment`: `docker php:8.3-cli`
  `checks`: `canonical php lint`, `tests/run-smoke-tests.sh`, `tests/check-diagnostics-contracts.sh`, `tests/check-benchmarks.sh`, `git diff --check`
  `result`: `pass`
  `notes`: `Ownership metadata, slice validation, graph diagnostics, and compiled ownership metadata changes passed full local verification.`
