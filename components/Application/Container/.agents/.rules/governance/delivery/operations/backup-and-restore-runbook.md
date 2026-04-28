# Backup And Restore Runbook

Version: 1.0.0
Status: Normative

Use this runbook when the repository owns durable state such as databases,
queues, file stores, or other recovery-critical data.

## Backup Contract

The child repository must define:

1. what is backed up
2. how the backup is created
3. where the backup artifact is stored
4. retention policy
5. who can execute or approve the operation

## Restore Contract

Restore should first target an empty, disposable, or isolated verification
surface whenever possible.

The child repository must define:

1. restore target
2. restore command or workflow
3. destructive safeguards
4. post-restore verification commands

## Recovery Verification Checklist

After restore, verify:

- the restore target is reachable
- critical records or assets exist as expected
- the application or service starts correctly against the restored state
- critical-path smoke checks succeed

## Drill Evidence Template

Record every recovery drill in release or operations evidence:

1. backup artifact identity and timestamp
2. restore target identity
3. verification summary
4. operator and reviewer sign-off

Stateful release without restore drill posture is weak by default.
