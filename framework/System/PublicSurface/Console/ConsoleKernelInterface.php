<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface\Console;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;

interface ConsoleKernelInterface
{
    /**
     * @param array<int|string, mixed> $arguments
     */
    public function run(string $commandName, array $arguments = []) : RuntimeResult;
}
