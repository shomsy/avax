<?php

declare(strict_types=1);

namespace Avax\Components\Events\System\PublicSurface;

interface EventsInterface
{
    public function dispatch(string $event, mixed $data = null): void;

    public function listen(string $event, callable $listener): void;
}