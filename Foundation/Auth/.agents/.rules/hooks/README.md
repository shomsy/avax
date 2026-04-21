# Hook Runtime

These scripts are the reusable runtime entrypoints that connect the governance
contracts to real client hook systems.

## Baseline Scripts

- `session-start.sh` initializes session state and context budget files
- `pre-task.sh` routes a prompt into lane, rule pack, pipeline, role, trust, and evidence targets
- `pre-tool-use.sh` enforces trust-tier and dangerous-operation checks
- `post-tool-use.sh` appends tool observations for the learning system
- `post-task.sh` closes the routed task with result and evidence
- `resolve-task-context.py` is the routing engine used by `pre-task.sh`

The scripts accept CLI flags and environment variables so different agent
clients can adapt them without rewriting the core logic.
