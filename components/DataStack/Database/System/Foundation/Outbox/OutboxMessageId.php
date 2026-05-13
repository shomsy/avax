<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Outbox;

/**
 * Outbox message identifier value object.
 *
 * Outbox is delivery reliability groundwork.
 * This is NOT an EventStore identifier.
 */
final readonly class OutboxMessageId
{
    public function __construct(
        public string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(value: bin2hex(random_bytes(16)));
    }
}
