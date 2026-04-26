# ReplicateCachedValues

Replicates cache entries across multiple nodes for high availability.

## What This Owns

- CacheReplication - sync strategy (sync, async)
- ReplicaSet - primary + replicas
- ReplicationTopology - ring, chain, mesh

## Triggers

HA requirement + multiple nodes

## Main Flow

```mermaid
flowchart TD
    A[Write to Primary] --> B{Mode?}
    B -->|SYNC| C[Wait for All Replicas]
    B -->|ASYNC| D[Return Immediately]
    C --> E{Ack from All?}
    E -->|Yes| F[Success]
    E -->|No| G[Failover]
```

## Debug

- Check replication lag
- Verify quorum writes
- Check failover triggers