# Failure Boundary Attributes Reference

## OnFailure

Declares how a specific exception type should be handled.

```php
#[OnFailure(ValidationFailed::class, respondWith: 422)]
#[OnFailure(AuthenticationFailed::class, respondWith: 401, messageKey: 'auth.unauthorized')]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$exceptionClass` | `class-string<Throwable>` | required | Exception type to catch |
| `$respondWith` | `int|null` | `null` | HTTP status code |
| `$messageKey` | `string|null` | `null` | Error message key |

**Repeatable:** Yes — multiple `#[OnFailure]` attributes per method.

## ReportFailure

Declares that failures should be reported to a specific channel.

```php
#[ReportFailure(channel: 'http')]
#[ReportFailure(channel: 'queue', level: 'critical')]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$channel` | `string` | `'default'` | Report channel name |
| `$level` | `string` | `'error'` | Severity level |
| `$includeStackTrace` | `bool` | `false` | Include stack trace in report |

## Retry

Declares retry behavior for failures.

```php
#[Retry(maxAttempts: 3)]
#[Retry(maxAttempts: 5, backoff: 'exponential', delayMs: 100, jitter: true)]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$maxAttempts` | `int` | `3` | Maximum retry attempts |
| `$backoff` | `string` | `'none'` | Backoff strategy: `none`, `linear`, `exponential` |
| `$delayMs` | `int` | `0` | Base delay in milliseconds |
| `$jitter` | `bool` | `false` | Add random jitter to delay |

## Timeout

Declares a timeout for the action execution.

```php
#[Timeout(milliseconds: 1500)]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$milliseconds` | `int` | `1000` | Timeout in milliseconds |

## Fallback

Declares a fallback handler to execute when the primary action fails.

```php
#[Fallback(UseCachedRates::class)]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$handlerClass` | `class-string` | required | Invokable fallback class |

## RecoverWith

Declares a recovery handler for complex failure recovery.

```php
#[RecoverWith(RecoverUserRegistration::class)]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$handlerClass` | `class-string` | required | Invokable recovery class |

## DeadLetter

Declares that exhausted failures should be sent to a dead letter queue.

```php
#[DeadLetter(queue: 'failed_jobs')]
#[DeadLetter(queue: 'failed_webhooks', metadata: ['source' => 'webhook'])]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$queue` | `string` | `'failed'` | Dead letter queue name |
| `$metadata` | `array` | `[]` | Additional metadata |

## Rethrow

Declares that failures should be rethrown rather than handled.

```php
#[Rethrow]
#[Rethrow(except: [ValidationFailed::class, AuthenticationFailed::class])]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$except` | `list<class-string<Throwable>>` | `[]` | Exceptions to still catch |
