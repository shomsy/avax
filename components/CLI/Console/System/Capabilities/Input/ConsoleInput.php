<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Input;

/**
 * Console input handler that parses argv into arguments and options.
 */
class ConsoleInput
{
    /** Positional arguments */
    private array $arguments = [];

    /** Named options (--key=value or -k) */
    private array $options = [];

    /**
     * Create from raw argv array.
     */
    public static function fromArgv(array $argv) : self
    {
        $input = new self();
        $input->parse($argv);

        return $input;
    }

    /**
     * Parse raw argv into arguments and options.
     */
    private function parse(array $argv) : void
    {
        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                // Long option: --key=value or --key
                $parts               = explode('=', substr($arg, 2), 2);
                $key                 = $parts[0];
                $this->options[$key] = $parts[1] ?? true;
            } elseif (str_starts_with($arg, '-') && strlen($arg) > 1) {
                // Short option: -k=value or -k or -abc
                $rest = substr($arg, 1);

                if (str_contains($rest, '=')) {
                    $parts                    = explode('=', $rest, 2);
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
    public static function make(array $arguments = [], array $options = []) : self
    {
        $input            = new self();
        $input->arguments = $arguments;
        $input->options   = $options;

        return $input;
    }

    /**
     * Get a positional argument by index or name.
     */
    public function getArgument(int|string $key, mixed $default = null) : mixed
    {
        if (is_int($key)) {
            return $this->arguments[$key] ?? $default;
        }

        // Named arguments mapped from command definition
        return $this->arguments[$key] ?? $default;
    }

    /**
     * Get all positional arguments.
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }

    /**
     * Get an option value by name.
     */
    public function getOption(string $key, mixed $default = null) : mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Get all options.
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Check if an option is present.
     */
    public function hasOption(string $key) : bool
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Get the first positional argument (commonly the command name).
     */
    public function getFirstArgument() : ?string
    {
        return $this->arguments[0] ?? null;
    }

    /**
     * Get all arguments except the first one.
     */
    public function getRemainingArguments() : array
    {
        return array_slice($this->arguments, 1);
    }
}
