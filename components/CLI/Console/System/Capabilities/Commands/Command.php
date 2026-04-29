<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Commands;

/**
 * Base class for CLI commands.
 */
abstract class Command
{
    protected string $name        = '';
    protected string $description = '';
    protected array  $arguments   = [];
    protected array  $options     = [];

    public function getName() : string
    {
        return $this->name;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }

    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Execute the command with given arguments.
     */
    abstract public function handle(array $args = []) : int;

    /**
     * Output a line to the console.
     */
    protected function line(string $message = '') : void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Output an info message.
     */
    protected function info(string $message) : void
    {
        echo "\033[32m{$message}\033[0m" . PHP_EOL;
    }

    /**
     * Output an error message.
     */
    protected function error(string $message) : void
    {
        echo "\033[31m{$message}\033[0m" . PHP_EOL;
    }

    /**
     * Output a warning message.
     */
    protected function warn(string $message) : void
    {
        echo "\033[33m{$message}\033[0m" . PHP_EOL;
    }

    /**
     * Output a comment.
     */
    protected function comment(string $message) : void
    {
        echo "\033[36m{$message}\033[0m" . PHP_EOL;
    }

    /**
     * Ask for user input.
     */
    protected function ask(string $question, ?string $default = null) : string
    {
        echo $question;
        if ($default !== null) {
            echo " [{$default}]";
        }
        echo ': ';

        $input = trim(fgets(STDIN));

        return $input === '' && $default !== null ? $default : $input;
    }

    /**
     * Confirm an action.
     */
    protected function confirm(string $question, bool $default = false) : bool
    {
        echo $question . ' (yes/no) [' . ($default ? 'yes' : 'no') . ']: ';
        $input = strtolower(trim(fgets(STDIN)));

        if ($input === '') {
            return $default;
        }

        return in_array($input, ['y', 'yes'], true);
    }
}
