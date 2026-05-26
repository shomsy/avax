# Data Correctness Evidence

## Task

<!-- What task requires this evidence? -->

## System of Record

<!-- Which system/component is the authoritative source of truth for this data? -->

## Derived State

<!-- Is any data derived from the system of record? How is it kept consistent? -->

## Cache Behavior

<!-- Caching strategy. TTL. Invalidation. Stale read tolerance. -->

## Transaction Boundary

<!-- Where does the transaction start and end? Isolation level? -->

## Idempotency

<!-- Is the operation idempotent? How is idempotency enforced? -->

## Retry Behavior

<!-- What happens on retry? Is it safe? -->

## Duplicate Handling

<!-- How are duplicates detected and handled? -->

## Ordering

<!-- Does ordering matter? How is it preserved? -->

## Consistency Expectation

<!-- Strong / eventual / read-your-writes / other -->

## Stale Read Behavior

<!-- What happens when a stale value is read? Is it acceptable? -->

## Schema Evolution

<!-- How will schema changes be handled? Backward compatibility? -->

## Failure Matrix

<!-- What fails, how it manifests, what the system does about it -->

## Reconciliation

<!-- How is inconsistency detected and corrected? -->

## Observability

<!-- Logging, metrics, alerting for data integrity issues -->

## Tests / Evidence

<!-- Links to tests proving data correctness properties -->

## Review Date

<!-- YYYY-MM-DD -->
