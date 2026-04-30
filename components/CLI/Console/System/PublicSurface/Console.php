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

    private string        $appName;
    private string        $appVersion;
    private ConsoleOutput $output;

    public function __construct(
        ?string        $name = null,
        ?string        $version = null,
        ?ConsoleOutput $output = null
    )
    {
        $this->appName    = $name ?? 'Avax Console';
        $this->appVersion = $version ?? '1.0.0';
        $this->output     = $output ?? new ConsoleOutput();
    }

    /**
     * Register a command instance.
     */
    public function register(Command $command) : self
    {
        $this->commands[$command->getName()] = $command;

        return $this;
    }

    /**
     * Check if a command is registered.
     */
    public function has(string $name) : bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * Resolve a command by name.
     */
    public function resolve(string $name) : ?Command
    {
        return $this->commands[$name] ?? null;
    }

    /**
     * Get all registered commands.
     */
    public function getCommands() : array
    {
        return $this->commands;
    }

    /**
     * Run the console application.
     *
     * Parses argv, resolves the command, and executes it.
     */
    public function run(?array $argv = null) : int
    {
        $argv = $argv ?? $_SERVER['argv'] ?? [];

        // Remove script name
        array_shift($argv);

        if (empty($argv)) {
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
            $this->output->line("{$this->appName} version {$this->appVersion}");

            return Command::SUCCESS;
        }

        // Resolve command
        $command = $this->resolve($commandName);

        if ($command === null) {
            $this->output->error("Command '{$commandName}' not found.");
            $this->output->newLine();
            $this->list();

            return Command::FAILURE;
        }

        // Parse remaining arguments
        $input = ConsoleInput::fromArgv($argv);

        return $command->run($input, $this->output);
    }

    /**
     * List all registered commands.
     */
    public function list() : void
    {
        $this->output->line("{$this->appName} version {$this->appVersion}");
        $this->output->newLine();
        $this->output->line('Usage:');
        $this->output->line('  <command> [arguments] [options]');
        $this->output->newLine();

        if (empty($this->commands)) {
            $this->output->line('No commands registered.');

            return;
        }

        $this->output->bold('Available commands:');
        $this->output->newLine();

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
            $this->output->line("  {$name}  {$desc}");
        }
    }

    /**
     * Get the output instance.
     */
    public function getOutput() : ConsoleOutput
    {
        return $this->output;
    }
}
