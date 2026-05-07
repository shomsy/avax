<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Flows\DispatchCommand;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;

final readonly class DispatchCommand
{
    public function dispatch(CommandBus $bus, Command $command) : mixed
    {
        return $bus->dispatch($command);
    }
}
