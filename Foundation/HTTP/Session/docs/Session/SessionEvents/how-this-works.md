# SessionEvents - How This Works

## Purpose

Event pub/sub system for session lifecycle.

## Events

- `session.stored` - Value stored
- `session.retrieved` - Value retrieved
- `session.deleted` - Value deleted
- `session.cleared` - Session cleared
- `session.id_regenerated` - ID rotated
- `session.terminated` - Session ended
- `session.actor_bound` - Actor bound
- `session.snapshot_taken` - Snapshot created
- `session.snapshot_restored` - Snapshot restored
- `session.transaction_committed` - Transaction committed
- `session.transaction_rolled_back` - Transaction rolled back

## Usage

```php
$events = new SessionEventBus();
$events->listen('session.stored', fn($event) => log($event));

$events->dispatch('session.stored', ['key' => 'user_id']);
```