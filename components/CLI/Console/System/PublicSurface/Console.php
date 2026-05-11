<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\PublicSurface;

use Avax\Components\CLI\Console\System\Capabilities\Input\ConsoleInput;
use Avax\Components\CLI\Console\System\Capabilities\Output\ConsoleOutput;

/**
 * Console application that can register and run commands.
 *
 * Provides command registry, input parsing, output writing,
 * and a run() method that resolves and executes commands.
 */
class Console
{
    /** @var array<string, Command> Registered commands */
    private array $commands = [];

    private readonly string $appName;

    private readonly string $appVersion;

    public function __construct(string|null $name = null, string|null $version = null,
        private readonly ConsoleOutput $consoleOutput = new ConsoleOutput(),
    ) {
        $this->appName = $name ?? 'Avax Console';
        $this->appVersion = $version ?? '1.0.0';
    }

    /**
     * Register a command instance.
     */
    public function register(Command $command): self
    {
        $this->commands[$command->getName()] = $command;

        return $this;
    }

    /**
     * Check if a command is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * Resolve a command by name.
     */
    public function resolve(string $name) : Command|null
    {
        return $this->commands[$name] ?? null;
    }

    /**
     * Get all registered commands.
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Run the console application.
     *
     * Parses argv, resolves the command, and executes it.
     */
    public function run(array|null $argv = null) : int
    {
        $argv ??= $_SERVER['argv'] ?? [];

        // Remove script name
        array_shift($argv);

        if ($argv === []) {
            $this->list();

            return Command::SUCCESS;
        }

        $commandName = $argv[0];
        array_shift($argv);

        // Handle built-in flags
        if ($commandName === '--help' || $commandName === '-h') {
            $this->list();

            return Command::SUCCESS;
        }

        if ($commandName === '--version' || $commandName === '-V') {
            $this->consoleOutput->line(sprintf('%s version %s', $this->appName, $this->appVersion));

            return Command::SUCCESS;
        }

        // Resolve command
        $command = $this->resolve($commandName);

        if (! $command instanceof Command) {
            $this->consoleOutput->error(sprintf("Command '%s' not found.", $commandName));
            $this->consoleOutput->newLine();
            $this->list();

            return Command::FAILURE;
        }

        // Parse remaining arguments
        $consoleInput = ConsoleInput::fromArgv($argv);

        return $command->run($consoleInput, $this->consoleOutput);
    }

    /**
     * List all registered commands.
     */
    public function list(): void
    {
        $this->consoleOutput->line(sprintf('%s version %s', $this->appName, $this->appVersion));
        $this->consoleOutput->newLine();
        $this->consoleOutput->line('Usage:');
        $this->consoleOutput->line('  <command> [arguments] [options]');
        $this->consoleOutput->newLine();

        if ($this->commands === []) {
            $this->consoleOutput->line('No commands registered.');

            return;
        }

        $this->consoleOutput->bold('Available commands:');
        $this->consoleOutput->newLine();

        // Calculate max command name length for alignment
        $maxNameLength = 0;

        foreach ($this->commands as $command) {
            $nameLength = mb_strlen($command->getName());

            if ($nameLength > $maxNameLength) {
                $maxNameLength = $nameLength;
            }
        }

        foreach ($this->commands as $command) {
            $name = str_pad($command->getName(), $maxNameLength);
            $desc = $command->getDescription();
            $this->consoleOutput->line(sprintf('  %s  %s', $name, $desc));
        }
    }

    /**
     * Get the output instance.
     */
    public function getOutput(): ConsoleOutput
    {
        return $this->consoleOutput;
    }
}
