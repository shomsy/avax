---
title: telemetry-events-subscribers-how-this-works
owner: foundation-database-telemetry
classification: internal
---

# Telemetry / Events / Subscribers How This Works

Event-driven logging/observability hooks.

## Triggers

EventBus::dispatch(QueryExecuted) → subscriber.react().

## Main units

DatabaseLoggerSubscriber.php: Logs SQL + bindings + duration to Psr\Log\LoggerInterface.

