# Blocked Validation

Date: 2026-05-06
Stage: 04 - Component Completion

Command:

```bash
vendor/bin/phpstan analyse components/Application/Facade --memory-limit=1G --error-format=raw --no-progress
```

Reason:

Approval usage limit rejected the escalated command needed by the local PHP wrapper.

The command was not rerun through an indirect workaround.

Next required action:

Rerun the command when approval/tooling is available.
