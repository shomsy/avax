<?php

declare(strict_types=1);

namespace Avax\Components\Events\System\Flows\DispatchEvent;

final class DispatchEvent
{
    public function dispatch(\Avax\Components\Events\System\PublicSurface\Events $events, string $name, mixed $data = null): void
    {
        $events->dispatch($name, $data);
    }
}