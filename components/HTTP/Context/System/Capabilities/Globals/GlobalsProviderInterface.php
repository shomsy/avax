<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\Capabilities\Globals;

/**
 * Read-only access to PHP runtime globals.
 */
interface GlobalsProviderInterface
{
    public function server(): array;
    public function query(): array;
    public function post(): array;
    public function cookies(): array;
    public function files(): array;
    public function session(): array;
}
