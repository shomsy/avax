# SessionAudit - How This Works

## Purpose

Structured audit logging for session operations.

## Features

- PSR-3 logger integration
- Structured JSON payloads
- Sensitive data masking
- Context capture (IP, session ID, user ID)

## Usage

```php
$audit = new SessionAudit($logger);
$audit->record('session.value_stored', ['key' => 'user_id']);
$audit->record('session.terminated', ['user_id' => 42, 'reason' => 'logout']);
```