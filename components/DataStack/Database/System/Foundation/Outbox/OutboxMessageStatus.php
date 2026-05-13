<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Outbox;

/**
 * Outbox message status enum.
 *
 * Outbox is delivery reliability, NOT EventStore.
 * EventStore is source of truth. Outbox ensures at-least-once delivery.
 */
enum OutboxMessageStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Failed = 'failed';
    case DeadLettered = 'dead_lettered';
}
