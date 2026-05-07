<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Flows\ExecuteConsoleCommand;

use Avax\Components\CLI\Console\System\Capabilities\Input\ConsoleInput;
use Avax\Components\CLI\Console\System\Capabilities\Output\ConsoleOutput;

final readonly class ExecuteConsoleCommand
{
    /**
     * @param array<string, mixed> $args
     */
    public function execute(string $name, array $args, ConsoleInput $input, ConsoleOutput $output) : int
    {
        $output->line("Executing command: {$name}");

        return 0;
    }
}
