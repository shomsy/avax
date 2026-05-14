<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\PublicSurface;

/**
 * Marker interface for command messages.
 *
 * A Command represents an intent to perform an action.
 * Commands are dispatched through the CommandBus and handled by exactly one handler.
 *
 * This interface is a marker that identifies a class as a command message.
 * It carries no behavior - the command name itself is the contract.
 *
 * Example:
 *   final readonly class RegisterUser implements Command {}
 */
interface Command
{
}
