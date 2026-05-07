<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Flows\RunConsoleCommand;

use Avax\Components\CLI\Console\System\Capabilities\Input\ConsoleInput;
use Avax\Components\CLI\Console\System\Capabilities\Output\ConsoleOutput;
use RuntimeException;

final readonly class RunConsoleCommand
{
    /**
     * @param array<string, mixed>  $args
     * @param array<string, object> $registry
     */
    public function run(string $name, array $args, array $registry, ConsoleInput $input, ConsoleOutput $output) : int
    {
        if (! isset($registry[$name])) {
            $output->line("Command not found: {$name}");

            return 1;
        }

        $command = $registry[$name];

        if (! method_exists($command, 'handle')) {
            throw new RuntimeException("Command {$name} does not have a handle method.");
        }

        return $command->handle($input, $output);
    }
}
