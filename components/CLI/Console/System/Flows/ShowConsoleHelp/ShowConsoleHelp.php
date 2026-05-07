<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Flows\ShowConsoleHelp;

use Avax\Components\CLI\Console\System\Capabilities\Output\ConsoleOutput;

final readonly class ShowConsoleHelp
{
    /**
     * @param array<string, string> $commands
     */
    public function show(array $commands, ConsoleOutput $output) : void
    {
        $output->line('Available commands:');
        foreach ($commands as $name => $description) {
            $output->line("  {$name} - {$description}");
        }
    }
}
