# Middleware Pipeline

## What It Is

A Middleware Pipeline is a series of checkpoints that an HTTP request passes through BEFORE reaching the actual Flow that handles it.

Think of it like airport security:
1. Check your ticket (authentication)
2. Check your ID (authorization)
3. Scan your bags (input validation)
4. Check if you're on a watchlist (rate limiting, IP blocking)
5. Then you get to the gate (the Flow that handles your request)

Each middleware can either:
- **Pass**: "This request looks fine, next checkpoint!"
- **Reject**: "Stop! This request is not allowed" (returns an error response)
- **Modify**: "I'll add something to this request" (like adding the authenticated user)

## What It Is NOT

- It is NOT the business logic that handles the request
- It is NOT a substitute for Flow-level authorization
- It is NOT a place for database queries or heavy computation
- It is NOT where you decide WHAT the request means, only whether it should proceed

## Common Confusion

**Confusion**: "I should put my business validation in middleware."
**Reality**: Middleware checks HTTP-level concerns (auth, rate limit, CORS, content type). Business validation belongs in the Flow. Middleware asks "should this request proceed?" The Flow asks "is this request correct?"

**Confusion**: "Middleware can change the request into something completely different."
**Reality**: Middleware can add metadata (like the authenticated user) but should not transform the core request. The Flow should receive the actual client request, not a middleware invention.

**Confusion**: "I can skip middleware if I trust the client."
**Reality**: Never skip security middleware based on trust. Every request is untrusted until proven otherwise. Middleware is defense-in-depth.
