<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\RunConsoleCommand;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;

final class RunConsoleCommand
{
    public function __construct(
        private readonly RuntimeContext $context,
    ) {
    }

    public function run(array $argv): int
    {
        $commandName = $argv[1] ?? 'help';

        $input = $this->readConsoleInput($argv);
        $command = $this->resolveConsoleCommand($commandName);

        $output = $this->executeConsoleCommand($command, $input);

        $this->writeConsoleOutput($output);

        return 0;
    }

    private function readConsoleInput(array $argv): array
    {
        return $argv;
    }

    private function resolveConsoleCommand(string $commandName): callable
    {
        return fn() => print "Command: {$commandName}\n";
    }

    private function executeConsoleCommand(callable $command, array $input): string
    {
        ob_start();
        $command($input);

        return ob_get_clean();
    }

    private function writeConsoleOutput(string $output): void
    {
        echo $output;
    }
}