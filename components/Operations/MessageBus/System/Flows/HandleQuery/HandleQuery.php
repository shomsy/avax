<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Flows\HandleQuery;

use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\QueryBus;

final readonly class HandleQuery
{
    public function handle(QueryBus $bus, object $query) : mixed
    {
        return $bus->dispatch($query);
    }
}
