# Term
Daemon

# Classification
Infrastructure — Long-Running Process

# Purpose
A background process that runs continuously, independent of user interaction, providing services such as scheduling, monitoring, or request handling.

# Why Allowed
The daemon concept originates from Unix system design and remains fundamental to infrastructure architecture. In modern systems, daemons power service discovery (consul agent), container orchestration (kubelet, containerd), message queuing (rabbitmq-server, redis-server), logging (rsyslog, fluentd), monitoring (prometheus, node_exporter), and CI/CD agents (github-runner, gitlab-runner). A daemon has clear characteristics: it starts at boot or on demand, runs in the background, handles requests or events continuously, manages its own lifecycle (restart on failure, graceful shutdown), and often communicates over IPC, sockets, or HTTP. It is not a one-shot script or a cron job.

# Allowed Contexts
- Unix system daemons (systemd services, init.d scripts)
- Container runtime daemons (containerd, dockerd)
- Service mesh sidecars (envoy, linkerd-proxy)
- Monitoring daemons (prometheus, node_exporter, datadog-agent)
- CI/CD runner daemons (github-runner, gitlab-runner)
- Message broker daemons (rabbitmq, kafka, nats-server)
- Cache daemons (redis-server, memcached)
- Log collection daemons (fluentd, logstash, vector)

# Forbidden Misuse
- Naming a short-lived CLI script a "daemon" when it exits after a single operation
- Creating a Daemons/ folder for general utility scripts
- Calling a cron job a "daemon" when it runs on a schedule and exits rather than running continuously
- Using "daemon" to describe a thread or goroutine that is managed by a parent process lifecycle

# Ecosystem References
- https://www.freedesktop.org/software/systemd/man/systemd.service.html
- https://containerd.io/
- https://www.consul.io/docs/agent
- https://prometheus.io/docs/introduction/overview/
- https://docs.fluentd.org/

# Allowed Patterns
- consulAgentDaemon
- prometheusScraperDaemon
- runnerRegistrationDaemon
- logCollectorDaemon
- healthCheckDaemon
- certRotationDaemon

# Forbidden Patterns
- Daemon (as a folder name)
- DaemonManager
- TaskDaemon (too vague — should specify what the daemon manages)
- DaemonHelper
- GenericDaemon
