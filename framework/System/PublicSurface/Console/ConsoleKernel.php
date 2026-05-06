<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface\Console;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Flows\RunConsoleCommand\RunConsoleCommand;

final readonly class ConsoleKernel implements ConsoleKernelInterface
{
    public function __construct(
        private RunConsoleCommand $runConsoleCommand,
    ) {
    }

    /**
     * @param  array<int|string, mixed>  $arguments
     */
    public function run(string $commandName, array $arguments = []): RuntimeResult
    {
        return $this->runConsoleCommand->run(
            arguments: array_merge([$commandName], $arguments),
        );
    }
}
