# Technical Glossary

## Purpose

This document records intentionally allowed technical and system-design terminology that is permitted in AvaX despite
the general governance rule against concept words as folder or class names.

These terms are legitimate distributed-systems and system-engineering concepts. They are allowed because:

- They name well-known patterns with established meanings across the industry.
- Renaming them would reduce clarity, not improve it.
- They solve specific, well-understood problems.
- Changing them to action phrases would lose the semantic weight that engineers rely on.

Each entry explains what the term is, what problem it solves, where it is used, and why it is allowed in AvaX.

---

## Entries

### RetryPolicy

**What it is:** A configuration object that defines how retries should behave — including maximum attempts, delay
strategy (fixed, exponential, jitter), and retry conditions.

**What problem it solves:** Transient failures (network blips, timeouts, rate limits) are inevitable in distributed
systems. A RetryPolicy provides a declarative, configurable way to handle them without hardcoding retry logic into every
caller.

**Where it is commonly used:** HTTP clients, database connections, message queue consumers, external API integrations,
resilience layers.

**Why it is allowed in AvaX:** "Policy" here refers to a specific, well-known systems-engineering concept from the Polly
library (.NET) and similar resilience frameworks. It is not a generic bucket — it is a concrete configuration artifact
that governs retry behavior.

**References:
** [Polly Retry Policy](https://github.com/App-vNext/Polly), [AWS Retry Strategies](https://docs.aws.amazon.com/architecture/framework/resiliency/retry-mechanisms.html)

---

### BackpressurePolicy

**What it is:** A configuration object that defines how a system should respond when downstream consumers cannot keep up
with incoming load — including rejection strategies, queue limits, and shedding rules.

**What problem it solves:** Without backpressure, fast producers can overwhelm slow consumers, leading to memory
exhaustion, cascading failures, and system collapse. A BackpressurePolicy provides controlled degradation under load.

**Where it is commonly used:** Message queues, stream processors, reactive systems, event loops, worker pools, async
pipelines.

**Why it is allowed in AvaX:** "Backpressure" is a fundamental concept from reactive systems engineering (Reactive
Manifesto). The "Policy" suffix follows the same convention as RetryPolicy — it is a configuration artifact, not a
generic bucket.

**References:
** [Reactive Manifesto — Responsive](https://www.reactivemanifesto.org/), [Akka Backpressure](https://doc.akka.io/docs/akka/current/stream/stream-rate.html)

---

### RateLimitPolicy

**What it is:** A configuration object that defines how request rate limiting should work — including window size,
maximum requests per window, key strategy, and rejection behavior.

**What problem it solves:** Rate limiting protects systems from abuse, overload, and unfair resource consumption. A
RateLimitPolicy centralizes these rules so they can be configured, audited, and changed without modifying business
logic.

**Where it is commonly used:** API gateways, public endpoints, authentication endpoints, payment processors, external
integrations.

**Why it is allowed in AvaX:** Rate limiting is a standard security and performance concept. The "Policy" suffix signals
that this is a configuration definition, not an implementation detail. It follows the established AvaX convention for
resilience configuration artifacts.

**References:
** [RFC 6585 — Additional HTTP Status Codes (429)](https://datatracker.ietf.org/doc/html/rfc6585), [NGINX Rate Limiting](https://docs.nginx.com/nginx/rate-limiting/)

---

### WorkerRestartPolicy

**What it is:** A configuration object that defines when and how a supervisor should restart a failed or unhealthy
worker — including conditions, delays, maximum restart attempts, and exponential backoff.

**What problem it solves:** Workers fail. A restart policy ensures they are restarted automatically within safe bounds,
preventing both abandonment (never restarting) and restart storms (infinite crash-restart loops).

**Where it is commonly used:** Supervisor systems, long-lived workers, queue consumers, daemon processes, container
orchestration.

**Why it is allowed in AvaX:** Restart policies are a standard concept in process supervision (Erlang/OTP supervisors,
Docker restart policies, systemd). The "Policy" suffix indicates a configuration artifact that governs restart behavior,
consistent with other policy terms in this glossary.

**References:
** [Erlang/OTP Supervision](https://www.erlang.org/doc/design_principles/sup_princ), [Docker Restart Policies](https://docs.docker.com/reference/cli/docker/container/run/#restart)

---

### TaskRetryPolicy

**What it is:** A configuration object that defines how failed background tasks should be retried — including attempt
limits, delay strategy, retry conditions, and dead-letter routing.

**What problem it solves:** Background tasks fail for many reasons (temporary unavailability, transient errors, resource
contention). A TaskRetryPolicy ensures retries happen predictably and safely, with proper limits to avoid infinite
loops.

**Where it is commonly used:** Task queues, background job processors, async task runners, scheduled task systems.

**Why it is allowed in AvaX:** Like other policy terms, this is a configuration artifact with a well-established
meaning. It is distinct from RetryPolicy because it is scoped specifically to the task execution plane, with
task-specific concerns (queue position, task priority, idempotency).

**References:
** [Celery Retry](https://docs.celeryq.dev/en/stable/userguide/tasks.html#retrying), [Sidekiq Retry](https://github.com/sidekiq/sidekiq/wiki/Error-Handling)
