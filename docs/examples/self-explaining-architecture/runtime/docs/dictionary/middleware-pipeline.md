# Middleware Pipeline

## What It Is

The middleware pipeline is an ordered sequence of handlers that wrap every incoming request. Each middleware can inspect, modify, or reject the request before it reaches the application handler, and can modify the response on the way back out.

## Why It Exists

Cross-cutting concerns (authentication, logging, CORS, rate limiting) must apply to many routes without being duplicated in each handler. The pipeline ensures these concerns are applied consistently in a defined order.

## Real-World Analogy

Like airport security checkpoints. Every passenger goes through the same sequence: document check, baggage scan, body scan. Each checkpoint can stop the passenger or let them proceed. The passenger does not go through arbitrary checkpoints in random order.

## Ownership

Owned by the Runtime component.

## Common Confusion

People think middleware is about request filtering. Middleware is about request processing in layers — each layer adds context or enforces rules, not just filters.

## What It Is NOT

- Middleware is NOT business logic
- Middleware is NOT route handlers
- Middleware is NOT a plugin system for random behavior
- Middleware is NOT a place for domain logic

## Common Mistakes

1. Adding domain-specific logic to middleware
2. Relying on middleware execution order without documenting it
3. Creating middleware that modifies request/response in ways other middleware cannot predict

## Relation to Other Concepts

- **Runtime**: Owns the pipeline execution
- **Router**: Determines which middleware stack applies to which route
- **Middleware**: Individual processing step in the pipeline
