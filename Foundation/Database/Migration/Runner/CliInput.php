<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner;

class CliInput
{
    public function __construct(private readonly array $rawArguments) {}

    /**
     * Check if a specific key exists in the arguments.
     */
    public function has(string $key) : bool
    {
        foreach ($this->rawArguments as $rawArgument) {
            if (str_starts_with((string) $rawArgument, '--' . $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the value of a specific key.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        foreach ($this->rawArguments as $rawArgument) {
            if (str_starts_with((string) $rawArgument, '--' . $key)) {
                [$k, $value] = explode('=', (string) $rawArgument, 2) + [1 => $default];

                return $value;
            }
        }

        return $default;
    }

    /**
     * Retrieve a raw argument by index.
     */
    public function getRawArgument(int $index) : string|null
    {
        return $this->rawArguments[$index] ?? null;
    }

    /**
     * Get all remaining arguments (after the command).
     */
    public function getRemainingArguments() : array
    {
        return array_slice($this->rawArguments, 2);
    }
}
