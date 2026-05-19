# V5.8-16: Database Lifecycle Security Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Security Design

## Rules

1. **Query lifecycle events must not log sensitive SQL bindings by default.**
2. **Redaction must be used for query telemetry.**
3. **Lifecycle events must not leak secrets.**
4. **Outbox messages must be serialized safely.**
5. **Event payloads must be versioned if durable.**
6. **Realtime/broadcast of DB lifecycle events is forbidden by default.**
7. **External publication must use afterCommit/outbox.**
8. **Raw SQL details must not be exposed in public events unless diagnostic mode is explicit.**

## Query Redaction

### Sensitive Data Categories

| Category        | Examples                                     | Redaction Strategy |
|-----------------|----------------------------------------------|--------------------|
| Passwords       | `password`, `password_confirmation`          | `***REDACTED***`   |
| Tokens          | `api_token`, `access_token`, `refresh_token` | `***REDACTED***`   |
| Secrets         | `secret`, `secret_key`, `api_key`            | `***REDACTED***`   |
| Email (partial) | `email`                                      | `u***@example.com` |
| PII             | `ssn`, `date_of_birth`, `phone`              | `***REDACTED***`   |
| Credit cards    | `card_number`, `cvv`                         | `***REDACTED***`   |

### Redaction in Query Events

```php
final readonly class QueryExecuted
{
    public function __construct(
        public string $sql,           // SQL with placeholders — already safe
        public array $bindings,       // REDACTED by default
        public string $connection,
        public float $durationMs,
        public int $rowCount,
    ) {}

    // Raw bindings available only in diagnostic mode
    public function rawBindings(): array
    {
        // Only available if diagnostic mode is explicitly enabled
        throw new LogicException('Raw bindings not available in production mode');
    }
}
```

### Redaction Implementation

```php
final readonly class RedactQueryBindings
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation',
        'api_token', 'access_token', 'refresh_token',
        'secret', 'secret_key', 'api_key',
        'ssn', 'date_of_birth', 'phone',
        'card_number', 'cvv',
    ];

    public function redact(array $bindings): array
    {
        $redacted = [];
        foreach ($bindings as $key => $value) {
            $lowerKey = strtolower((string) $key);
            foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
                if (str_contains($lowerKey, $sensitiveKey)) {
                    $redacted[$key] = '***REDACTED***';
                    continue 2;
                }
            }
            $redacted[$key] = $value;
        }
        return $redacted;
    }
}
```

## Outbox Message Security

- **Serialization:** Use safe serialization (JSON) — no PHP `serialize()` for external messages.
- **Payload versioning:** Include schema version in outbox messages for forward compatibility.
- **Sensitive fields:** Redact sensitive fields before storing in outbox table.
- **Encryption:** Outbox table should be encrypted at rest (DB-level encryption).

## Event Payload Security

- **Versioned payloads:** Include `schema_version` in durable event payloads.
- **No secrets:** Event payloads must not contain passwords, tokens, or secrets.
- **PII awareness:** Event payloads containing PII must be documented and handled according to privacy policy.

## Realtime/Broadcast Restriction

- **DB lifecycle events must NOT be broadcast in realtime by default.**
- **External publication must use afterCommit** — ensuring the DB transaction succeeded first.
- **Outbox must be used for durable external publication** — ensuring at-least-once delivery.

## Security Gate Design (Future Tooling)

`check-db-query-redaction.php` — fails if:

- Query telemetry logs raw sensitive bindings
- Redaction is not applied by default
- Raw bindings are accessible without explicit diagnostic mode flag

## Next Allowed Action

V5.8-17 Observability Design.
