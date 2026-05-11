<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Input;

/**
 * Console input handler that parses argv into arguments and options.
 */
class ConsoleInput
{
    /** Positional arguments (indexed) */
    private array $arguments = [];

    /** @var array<string, mixed> Named arguments mapped by declared argument name */
    private array $namedArguments = [];

    /** Named options (--key=value or -k) */
    private array $options = [];

    /**
     * Create from raw argv array.
     */
    public static function fromArgv(array $argv): self
    {
        $input = new self();
        $input->parse($argv);

        return $input;
    }

    /**
     * Parse raw argv into arguments and options.
     */
    private function parse(array $argv): void
    {
        foreach ($argv as $arg) {
            if (str_starts_with((string) $arg, '--')) {
                // Long option: --key=value or --key
                $parts = explode('=', substr((string) $arg, 2), 2);
                $key = $parts[0];
                $this->options[$key] = $parts[1] ?? true;
            } elseif (str_starts_with((string) $arg, '-') && strlen((string) $arg) > 1) {
                // Short option: -k=value or -k or -abc
                $rest = substr((string) $arg, 1);

                if (str_contains($rest, '=')) {
                    $parts = explode('=', $rest, 2);
                    $this->options[$parts[0]] = $parts[1];
                } elseif (strlen($rest) > 1) {
                    // Multiple short flags: -abc → a, b, c
                    foreach (str_split($rest) as $char) {
                        $this->options[$char] = true;
                    }
                } else {
                    $this->options[$rest] = true;
                }
            } else {
                // Positional argument
                $this->arguments[] = $arg;
            }
        }
    }

    /**
     * Create with predefined arguments and options.
     */
    public static function make(array $arguments = [], array $options = []): self
    {
        $input = new self();
        $input->arguments = $arguments;
        $input->options = $options;

        return $input;
    }

    /**
     * Map positional arguments to declared argument names from command signature.
     *
     * @param array<string> $argumentNames Ordered list of argument names from signature
     */
    public function bindNamedArguments(array $argumentNames) : self
    {
        $this->namedArguments = [];

        foreach ($argumentNames as $index => $name) {
            if (isset($this->arguments[$index])) {
                $this->namedArguments[$name] = $this->arguments[$index];
            }
        }

        return $this;
    }

    /**
     * Get a positional argument by index or name.
     */
    public function getArgument(int|string $key, mixed $default = null): mixed
    {
        if (is_string($key) && array_key_exists($key, $this->namedArguments)) {
            return $this->namedArguments[$key];
        }

        return $this->arguments[$key] ?? $default;
    }

    /**
     * Check if a positional argument exists by index or name.
     */
    public function hasArgument(int|string $key) : bool
    {
        if (is_string($key)) {
            return array_key_exists($key, $this->namedArguments);
        }

        return array_key_exists($key, $this->arguments);
    }

    /**
     * Get all positional arguments.
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get an option value by name.
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Get all options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Check if an option is present.
     */
    public function hasOption(string $key): bool
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Get the first positional argument (commonly the command name).
     */
    public function getFirstArgument() : string|null
    {
        return $this->arguments[0] ?? null;
    }

    /**
     * Get all arguments except the first one.
     */
    public function getRemainingArguments(): array
    {
        return array_slice($this->arguments, 1);
    }
}
