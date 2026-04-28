<?php

declare(strict_types=1);

namespace Avax\Components\Events\System\Flows\DispatchEvent;

use Avax\Components\Events\System\PublicSurface\Events;

final class DispatchEvent
{
    public function dispatch(Events $events, string $name, mixed $data = null): void
    {
        $events->dispatch($name, $data);
    }
}