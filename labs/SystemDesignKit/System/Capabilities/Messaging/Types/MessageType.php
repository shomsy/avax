<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Types;

/**
 * Message type taxonomy.
 *
 * @experimental V3 labs
 *
 * Defines the semantic category of a message in the system.
 */
enum MessageType: string
{
    case Command = 'command';
    case Event   = 'event';
    case Message = 'message';
    case Job     = 'job';

    /**
     * Whether this type expects a response.
     */
    public function expectsResponse() : bool
    {
        return $this === self::Command;
    }

    /**
     * Whether this type represents a fact that already happened.
     */
    public function isFact() : bool
    {
        return $this === self::Event;
    }

    /**
     * Whether this type is fire-and-forget.
     */
    public function isFireAndForget() : bool
    {
        return in_array($this, [self::Event, self::Job], true);
    }

    /**
     * Whether this type requires idempotency.
     */
    public function requiresIdempotency() : bool
    {
        return in_array($this, [self::Command, self::Event], true);
    }

    /**
     * Human-readable description.
     */
    public function description() : string
    {
        return match ($this) {
            self::Command => 'An instruction to perform an action. Expects execution and may return a result.',
            self::Event   => 'A fact that something has happened. Fire-and-forget, no response expected.',
            self::Message => 'A generic data transfer between components. May or may not expect a response.',
            self::Job     => 'A background unit of work. Asynchronous execution, fire-and-forget.',
        };
    }
}
