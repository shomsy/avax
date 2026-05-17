# Term
Worker

# Classification
Infrastructure — Execution Unit

# Purpose
A long-lived process or thread that pulls work from a queue, channel, or event stream and executes it, reporting results or failures back to the coordinating system.

# Why Allowed
Workers are a fundamental pattern in distributed and concurrent systems. Message queue workers process jobs from RabbitMQ, Kafka, Redis (Bull, Sidekiq), or AWS SQS. Web server workers handle incoming HTTP requests in PHP-FPM, Gunicorn, or Puma. Background job workers execute deferred tasks (Celery, Hangfire, BullMQ). Build system workers compile files in parallel (webpack workers, esbuild workers). CI/CD runners are workers that execute pipeline jobs. A worker has clear characteristics: it runs continuously, pulls or receives work items from a source, executes them, reports success or failure, and handles backpressure, retries, and graceful shutdown. It is distinct from a daemon (which provides services) and a scheduler (which decides work placement).

# Allowed Contexts
- Message queue workers (BullMQ, Sidekiq, Celery, RabbitMQ consumers)
- Web server workers (PHP-FPM, Gunicorn, Puma, Uvicorn workers)
- Background job workers (Hangfire, Bull, agenda, node-cron workers)
- Build system workers (webpack parallel workers, esbuild workers)
- CI/CD runner workers (GitHub Actions runner, GitLab runner)
- Browser Web Workers and Service Workers
- Distributed task workers (Ray workers, Dask workers, Temporal workers)

# Forbidden Misuse
- Naming a one-shot script a "worker" when it does not run continuously and pull work from a source
- Creating a Workers/ folder for general utility functions
- Calling a CLI command a "worker" when it is invoked manually and exits after a single operation
- Using "worker" to describe a thread that is directly spawned and managed inline without a work queue

# Ecosystem References
- https://github.com/OptimalBits/bull
- https://sidekiq.org/
- https://docs.celeryq.dev/
- https://www.php.net/manual/en/book.fpm.php
- https://developer.mozilla.org/en-US/docs/Web/API/Web_Workers_API
- https://docs.github.com/en/actions/hosting-your-own-runners

# Allowed Patterns
- jobQueueWorker
- httpServerWorker
- buildParallelWorker
- ciRunnerWorker
- mailProcessingWorker
- eventStreamWorker

# Forbidden Patterns
- Worker (as a folder name)
- WorkerManager
- TaskWorker (too vague — should specify what work is being processed)
- WorkerHelper
- GenericWorker
