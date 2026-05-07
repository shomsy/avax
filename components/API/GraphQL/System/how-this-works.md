# API GraphQL

The GraphQL component owns the V2 API engine slice for schema and resolver discipline.

It provides a public schema model, operation validation, query and mutation execution, batching strategy, authorization
checks, complexity/depth guards, and resolver timing evidence. It intentionally does not depend on a specific external
GraphQL runtime yet; this slice proves AvaX-owned contracts and safety behavior first.

It does not own HTTP routing, OpenAPI rendering, REST resources, JSON:API documents, webhooks, or RPC endpoints.
