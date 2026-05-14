<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\PublicSurface;

/**
 * Marker interface for query messages.
 *
 * A Query represents a request for data or information.
 * Queries are dispatched through the QueryBus and handled by exactly one handler.
 * Queries must not cause side effects.
 *
 * This interface is a marker that identifies a class as a query message.
 * It carries no behavior - the query name and response type are the contract.
 *
 * Example:
 *   final readonly class GetUserProfile implements Query {}
 */
interface Query
{
}
