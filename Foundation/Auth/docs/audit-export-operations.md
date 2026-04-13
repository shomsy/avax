# Audit Export Operations

This package now treats audit export as an explicit operational lane, not as a
debugging afterthought.

## Export Adapters

- JSON lines export for durable SIEM ingestion
- syslog export through an adapter boundary
- webhook export for downstream security tooling
- queue export for internal asynchronous pipelines

## Correlation

- wrap the package audit log with `CorrelatingAuditLog` or use
  `Auth::configuration()->withAuditCorrelationId(...)`
- correlation ids must be stable for one request, login journey, or incident
  trace
- exported payloads keep `correlation_id` as a first-class field

## Tamper Evidence

- JSON lines export can chain each record with `previous_hash` and
  `record_hash`
- downstream storage should preserve record order
- legal hold keeps chained evidence intact even when normal retention would
  anonymize or purge it

## Notification Rules

The reference notification adapter routes:

- refresh token reuse
- admin elevation
- factor removal
- password change after suspicious activity

Applications may extend the mapping, but these are the baseline high-signal
events.
