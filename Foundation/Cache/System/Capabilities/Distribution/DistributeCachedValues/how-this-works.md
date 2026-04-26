# DistributeCachedValues

Distributes cache across cluster nodes using consistent hashing.

## What This Owns

- ConsistentHashRing - virtual nodes for distribution
- CacheNodeId - node identity
- CacheNodeStatus - healthy/unhealthy
- DetectUnhealthyCacheNode - health checking

## Triggers

Multi-node cache cluster

## Main Flow

```mermaid
flowchart TD
    A[Key] --> B[Hash Ring]
    B --> C[Virtual Nodes]
    C --> D[Primary Node]
    D --> E{Route to Node}
    E -->|Healthy| F[Execute]
    E -->|Unhealthy| G[Next Node]
```

## Debug

- Check ring has >= 150 virtual nodes
- Verify node health tracking
- Check failover works