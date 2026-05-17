# Term
Scheduler

# Classification
Infrastructure — Orchestration Component

# Purpose
A component that decides when, where, and in what order tasks, jobs, or workloads should execute based on constraints such as timing, priority, resource availability, or dependency graphs.

# Why Allowed
Scheduling is a core infrastructure concern across computing. Operating systems schedule threads and processes (CFS in Linux, Windows scheduler). Container orchestrators schedule pods onto nodes (Kubernetes scheduler with affinity, taints, and resource constraints). CI/CD systems schedule pipeline jobs across runners. Task queues schedule deferred work (Celery, Bull, Sidekiq, cron-based schedulers). Database query optimizers include execution schedulers. A scheduler has a well-defined responsibility: it accepts a set of pending work items, applies scheduling policy (FIFO, priority, fair-share, deadline, constraint-based), and dispatches them to available executors. It does not execute the work itself — it decides when and where work runs.

# Allowed Contexts
- OS process and thread schedulers (Linux CFS, Windows scheduler, Go runtime scheduler)
- Container orchestration schedulers (Kubernetes scheduler, Nomad)
- CI/CD job schedulers (GitHub Actions, GitLab CI, Jenkins)
- Task queue schedulers (Celery, Bull, Sidekiq, Hangfire)
- Cron and time-based schedulers (systemd timers, cron, APScheduler)
- Database query schedulers (query execution planning, connection pool scheduling)
- Distributed work schedulers (Ray, Apache Airflow, Temporal)

# Forbidden Misuse
- Naming a simple setTimeout or setInterval wrapper a "scheduler" when it performs no policy-based decision
- Creating a Schedulers/ folder for general timing utilities
- Calling a retry mechanism a "scheduler" when it only repeats failed work without deciding placement or ordering
- Using "scheduler" to describe a static cron entry that has no dynamic decision logic

# Ecosystem References
- https://kubernetes.io/docs/concepts/scheduling-eviction/
- https://www.kernel.org/doc/html/latest/scheduler/index.html
- https://docs.celeryproject.org/
- https://airflow.apache.org/
- https://nomadproject.io/

# Allowed Patterns
- podScheduler
- jobQueueScheduler
- cronTaskScheduler
- workloadScheduler
- retryBackoffScheduler
- resourceAwareScheduler

# Forbidden Patterns
- Scheduler (as a folder name)
- SchedulerManager
- TaskScheduler (too vague — should specify what is being scheduled)
- SchedulerHelper
- GenericScheduler
