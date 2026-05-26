# Queue Component

> This component owns asynchronous job execution, message queuing, and background processing for the AvaX platform.

## What Lives Here

- **PublicSurface/** — Queue API: dispatch, process, retry, fail
- **Capabilities/** — Queue dispatcher, worker, job serializer, failure handler
- **Configuration/** — Queue connection configuration, worker registration
- **Foundation/** — Job, JobId, QueueName, JobResult value objects

## What Does NOT Live Here

- Job scheduling or cron (belongs in Scheduler component)
- Event processing (belongs in Events component)
- Business logic of individual jobs (belongs in domain components)
- Worker process management (belongs in deploy/runtime infrastructure)

## Ownership Model

Owned by the Platform Core team. Changes to queue behavior, delivery guarantees, or failure semantics require core team review.

## Architectural Intent

Queue provides async execution with at-least-once delivery guarantees. Jobs are dispatched to queues and processed by workers. Failed jobs are retried with configurable policies. Poison messages are moved to a dead-letter queue for manual inspection.

## Common Mistakes

1. **Putting too much logic in jobs** — Jobs should be thin wrappers that call domain capabilities.
2. **Ignoring idempotency** — At-least-once delivery means handlers must be idempotent.
3. **Not handling serialization** — Jobs must be serializable; closures and resource handles break serialization.

## Entry Points

- Start with `PublicSurface/QueueFacade.php` for dispatching jobs
- Start with `Capabilities/Worker.php` for processing jobs
