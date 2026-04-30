<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\RunConsoleCommand;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;

final class RunConsoleCommand
{
    public function __construct(
        private RuntimeInterface $runtime,
    ) {}

    public function getRuntimeContext() : RuntimeContext
    {
        return $this->runtime->context();
    }

    public function run(mixed ...$args) : RuntimeResult
    {
        $argv = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $argv = array_merge($argv, $arg);
            } else {
                $argv[] = $arg;
            }
        }

        $commandName = $argv[0] ?? 'help';

        $input = $this->readConsoleInput($argv);
        $command = $this->resolveConsoleCommand($commandName);

        $output = $this->executeConsoleCommand($command, $input);

        $this->writeConsoleOutput($output);

        return RuntimeResult::fromConsoleOutput(output: $output);
    }

    private function readConsoleInput(array $argv) : array
    {
        return $argv;
    }

    private function resolveConsoleCommand(string $commandName) : callable
    {
        return static fn () => print "Command: {$commandName}\n";
    }

    private function executeConsoleCommand(callable $command, array $input) : string
    {
        ob_start();
        $command($input);

        return ob_get_clean();
    }

    private function writeConsoleOutput(string $output) : void
    {
        echo $output;
    }
}
