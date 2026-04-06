# Auth Framework — Project Memory

This file contains long-term memory and key facts about the Auth framework project.

## Core Facts

| Fact | Value |
|------|-------|
| Project Name | Avax Auth |
| Type | Pure PHP 8.3+ authentication/authorization framework |
| Architecture | Feature-sliced (vertical-slice) |
| Namespace | `Avax\Auth` |
| Min PHP | 8.3 |

## Design Decisions

### Why Feature-Sliced?

- Flow-first structure: `Login/` contains all login-related code
- Easy to navigate: Predictable file locations
- Clear boundaries: Each feature is self-contained

### Why Pure PHP?

- No framework dependencies
- Works with any PHP project
- Maximum flexibility for users

### Security First

- `#[SensitiveParameter]` for all credentials
- Generic auth failure messages
- Rate limiting by default
- Password hashing with bcrypt/argon2

## Key Files

| File | Purpose |
|------|---------|
| `Authenticator.php` | Main entry point |
| `Actions/Login.php` | Login use case |
| `Adapters/Identity.php` | User authentication |
| `Contracts/AuthInterface.php` | Auth contract |

## Current Status

- Initial framework structure in place
- Agent harness configured
- Feature-sliced refactoring planned

## Learning Log

### 2026-04-06

- Installed agent-harness framework
- Configured for PHP 8.3+ framework
- Set vertical-slice architecture

## Update This File

When significant decisions or changes occur, update this file so future sessions know the context.