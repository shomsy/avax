# Auth Framework — Product Context

## Product Vision

A pure PHP 8.3+ authentication and authorization framework that provides secure, flexible, and framework-agnostic
identity management.

## Problem Statement

Developers need a secure, extensible auth solution that:

- Works with any PHP project (no framework lock-in)
- Has security built-in, not bolted on
- Is easy to understand and customize

## Solution

Avax Auth — a feature-sliced, DSL-driven PHP framework for auth.

## Value Propositions

| For                | Value                                      |
|--------------------|--------------------------------------------|
| **Developers**     | Quick integration, clear APIs, no magic    |
| **End Users**      | Secure login, transparent session handling |
| **Security Teams** | OWASP-compliant, audit-friendly            |

## Roadmap

### Phase 1 (Current)

- [x] Core auth actions (login, logout, register)
- [x] Identity adapters
- [x] Basic session management
- [ ] Feature-sliced refactoring
- [ ] Composer package setup

### Phase 2

- [ ] JWT support
- [ ] Role-based access control
- [ ] Rate limiting middleware
- [ ] Password reset flow

### Phase 3

- [ ] OAuth2/OpenID Connect adapters
- [ ] Multi-factor authentication
- [ ] Session management UI
- [ ] Full test coverage