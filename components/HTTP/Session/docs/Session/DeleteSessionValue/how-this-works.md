# DeleteSessionValue - How This Works

## Purpose

Handle deleting values from session store.

## Flow

1. `ResolveSessionDeleteKey` - Normalize key
2. `DropStoredSessionValue` - Remove from store
3. `RecordSessionValueDeleted` - Audit log
4. `PublishSessionValueDeleted` - Dispatch event
5. `RefreshSessionActivity` - Update last activity