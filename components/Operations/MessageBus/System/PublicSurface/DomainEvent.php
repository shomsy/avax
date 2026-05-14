<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\PublicSurface;

/**
 * Marker interface for domain event messages.
 *
 * A DomainEvent represents a fact that has already happened in the domain.
 * Events are past-tense, immutable records of meaningful domain changes.
 * Multiple listeners may react to a single event.
 *
 * This interface is a marker that identifies a class as a domain event.
 * It carries no behavior - the event name and data are the contract.
 *
 * Example:
 *   final readonly class UserRegistered implements DomainEvent {}
 */
interface DomainEvent
{
}
