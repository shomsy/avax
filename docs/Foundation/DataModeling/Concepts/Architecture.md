# Architecture

DataModeling is organized around collection capabilities.

Reading order:

1. Root façade: `Arrhae` or `Collection`
2. Capability slice under `Collections/*`
3. Internal helper only when needed
4. Component exception when behavior must fail explicitly

This keeps the component simple without falling back to generic utility buckets.
