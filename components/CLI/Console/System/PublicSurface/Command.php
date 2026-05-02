<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\PublicSurface;

use Avax\Components\CLI\Console\System\Capabilities\Input\ConsoleInput;
use Avax\Components\CLI\Console\System\Capabilities\Output\ConsoleOutput;

/**
 * Abstract base class for CLI commands.
 *
 * Provides name, description, signature, arguments, options,
 * and exit code constants. Subclasses implement handle().
 */
abstract class Command
{
    /** Exit code: command executed successfully */
    public const SUCCESS = 0;

    /** Exit code: command failed */
    public const FAILURE = 1;

    /** Exit code: invalid input or arguments */
    public const INVALID = 2;

    /** Command name (e.g. "make:controller") */
    protected string $name = '';

    /** Command description shown in help */
    protected string $description = '';

    /** Command signature (e.g. "make:controller {name}") */
    protected string $signature = '';

    /** Expected arguments [{name}, {name?}, ...] */
    protected array $arguments = [];

    /** Expected options [--option, --option=value, ...] */
    protected array $options = [];

    /** Input instance for the current run */
    protected ConsoleInput $input;

    /** Output instance for the current run */
    protected ConsoleOutput $output;

    /**
     * Run the command with given input and output.
     */
    public function run(ConsoleInput $consoleInput, ConsoleOutput $consoleOutput): int
    {
        $this->input = $consoleInput;
        $this->output = $consoleOutput;

        return $this->handle();
    }

    /**
     * Handle the command execution.
     * Must return an exit code (SUCCESS, FAILURE, or INVALID).
     */
    abstract protected function handle(): int;

    /**
     * Get the command name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the command description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the command signature.
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Get the expected arguments.
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get the expected options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get an argument value by name.
     */
    protected function argument(string $key, mixed $default = null): mixed
    {
        return $this->input->getArgument($key) ?? $default;
    }

    /**
     * Get an option value by name.
     */
    protected function option(string $key, mixed $default = null): mixed
    {
        return $this->input->getOption($key) ?? $default;
    }

    /**
     * Check if an option is present.
     */
    protected function hasOption(string $key): bool
    {
        return $this->input->hasOption($key);
    }

    /**
     * Output a plain line.
     */
    protected function line(string $message = ''): void
    {
        $this->output->line($message);
    }

    /**
     * Output an info message.
     */
    protected function info(string $message): void
    {
        $this->output->info($message);
    }

    /**
     * Output an error message.
     */
    protected function error(string $message): void
    {
        $this->output->error($message);
    }

    /**
     * Output a warning message.
     */
    protected function warn(string $message): void
    {
        $this->output->warn($message);
    }

    /**
     * Output a comment message.
     */
    protected function comment(string $message): void
    {
        $this->output->comment($message);
    }

    /**
     * Ask a question.
     */
    protected function ask(string $question, ?string $default = null): string
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Confirm an action.
     */
    protected function confirm(string $question, bool $default = false): bool
    {
        return $this->output->confirm($question, $default);
    }
}
