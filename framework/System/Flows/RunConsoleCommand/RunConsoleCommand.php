<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\RunConsoleCommand;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Closure;

final readonly class RunConsoleCommand
{
    /** @var array<string, Closure> */
    private array $commandOverrides = [];

    public function __construct(
        private RuntimeInterface $runtime,
    ) {}

    /**
     * @param array<string, Closure> $commands
     */
    public function withCommands(array $commands) : self
    {
        $clone                   = clone $this;
        $clone->commandOverrides = $commands;

        return $clone;
    }

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
        $commandArgs = array_slice($argv, 1);

        $input = $this->readConsoleInput($commandArgs);
        $command = $this->resolveConsoleCommand($commandName);

        $output = $this->executeConsoleCommand($command, $input);

        $this->writeConsoleOutput($output);

        return RuntimeResult::fromConsoleOutput(output: $output);
    }

    /**
     * @return list<string>
     */
    private function readConsoleInput(array $argv) : array
    {
        return $argv;
    }

    /**
     * @return callable(array<string>): string
     */
    private function resolveConsoleCommand(string $commandName) : callable
    {
        // Check command overrides first (e.g., pre-commit)
        if (isset($this->commandOverrides[$commandName])) {
            return $this->commandOverrides[$commandName];
        }

        // Check runtime registered commands
        $runtimeCommands = $this->runtime->consoleCommands();

        // Built-in commands
        return $runtimeCommands[$commandName] ?? $this->getBuiltInCommand($commandName);
    }

    /**
     * @return callable(array<string>): string
     */
    private function getBuiltInCommand(string $commandName) : callable
    {
        return match ($commandName) {
            'help'       => $this->showHelp(...),
            'pre-commit' => static fn (array $args) => $this->handlePreCommit($args),
            default      => static fn () : string => sprintf("Unknown command: %s%s", $commandName, PHP_EOL),
        };
    }

    /**
     * Show general help.
     */
    private function showHelp() : string
    {
        return <<<'HELP'
            Avax Console
            ============
            
            Usage: php avax <command> [options]
            
            Commands:
              pre-commit     Run pre-commit discipline check
              help          Show this help
            
            Examples:
              php avax pre-commit
              php avax help
            HELP;
    }

    /**
     * @param callable(array<string>): string $command
     */
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
