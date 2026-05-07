<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Flows\DispatchTransactionally;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use Avax\Components\Operations\MessageBus\System\PublicSurface\DomainEvent;

final readonly class DispatchTransactionally
{
    public function dispatch(CommandBus $commandBus, EventBus $eventBus, Command $command, DomainEvent ...$events) : void
    {
        foreach ($events as $event) {
            $eventBus->dispatch($event);
        }

        $commandBus->dispatch($command);
    }
}
