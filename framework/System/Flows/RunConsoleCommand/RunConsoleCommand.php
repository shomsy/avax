<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\RunConsoleCommand;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\PreCommit;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Closure;

final readonly class RunConsoleCommand
{
    /**
     * @param  array<string, Closure>  $commandOverrides
     */
    public function __construct(
        private RuntimeInterface $runtime,
        private array $commandOverrides = [],
    ) {
    }

    /**
     * @param  array<string, Closure>  $commands
     */
    public function withCommands(array $commands): self
    {
        return new self(
            runtime         : $this->runtime,
            commandOverrides: $commands,
        );
    }

    public function getRuntimeContext(): RuntimeContext
    {
        return $this->runtime->context();
    }

    public function run(mixed ...$args): RuntimeResult
    {
        $argv = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $argv = array_merge($argv, array_values($arg));
            } else {
                $argv[] = $arg;
            }
        }

        $commandName = (string) ($argv[0] ?? 'help');
        $commandArgs = array_map(
            callback: static fn (mixed $arg): string => (string) $arg,
            array   : array_slice($argv, 1),
        );

        $input = $this->readConsoleInput($commandArgs);
        $command = $this->resolveConsoleCommand($commandName);

        $output = $this->executeConsoleCommand($command, $input);

        $this->writeConsoleOutput($output);

        return RuntimeResult::fromConsoleOutput(output: $output);
    }

    /**
     * @param  list<string>  $argv
     * @return list<string>
     */
    private function readConsoleInput(array $argv): array
    {
        return $argv;
    }

    /**
     * @return callable(list<string>): string
     */
    private function resolveConsoleCommand(string $commandName): callable
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
     * @return callable(list<string>): string
     */
    private function getBuiltInCommand(string $commandName): callable
    {
        return match ($commandName) {
            'help' => $this->showHelp(...),
            'pre-commit' => fn (array $args): string => $this->handlePreCommit(args: array_values($args)),
            default => static fn (array $args): string => sprintf('Unknown command: %s%s', $commandName, PHP_EOL),
        };
    }

    /**
     * @param  list<string>  $args
     */
    private function showHelp(array $args = []): string
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
     * @param  list<string>  $args
     */
    private function handlePreCommit(array $args): string
    {
        $dryRun = ! in_array('--fix', $args, true);
        $full = in_array('--full', $args, true);

        $config = new PreCommitConfig();
        $config->setDryRun($dryRun);

        $preCommit = new PreCommit($config, [], ! $full);
        $result = $preCommit->run();

        return $result->getSummaryText();
    }

    /**
     * @param  callable(list<string>): string  $command
     * @param  list<string>  $input
     */
    private function executeConsoleCommand(callable $command, array $input): string
    {
        ob_start();
        $result = $command($input);
        $output = ob_get_clean();

        return ($output === false ? '' : $output).$result;
    }

    private function writeConsoleOutput(string $output): void
    {
        echo $output;
    }
}
