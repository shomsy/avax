# ControlConsistency

Ensures cache reads/writes follow configured consistency guarantees.

## What This Owns

- EventualConsistency
- StrongLocalConsistency
- ReadYourWritesConsistency
- ConsistencyWindow
- DecideCacheReadConsistency
- DecideCacheWriteConsistency

## Triggers

System operation type + consistency level config

## Main Flow

```mermaid
flowchart TD
    A[System Operation] --> B{Level?}
    B -->|STRONG| C[Read/Write Primary]
    B -->|EVENTUAL| D[Read Replica OK]
    B -->|LOCAL| E[Local Only]
    B -->|READ_YOUR_WRITES| F[Check Write Timestamp]
```

## Debug

- Check ConsistencyWindow timing
- Verify read/write decision matches level