<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Output;

use Avax\Components\CLI\Console\System\Capabilities\UI\Confirm;
use Avax\Components\CLI\Console\System\Capabilities\UI\ProgressBar;
use Avax\Components\CLI\Console\System\Capabilities\UI\Question;
use Avax\Components\CLI\Console\System\Capabilities\UI\Table;

/**
 * Console output handler with formatted output methods.
 */
class ConsoleOutput
{
    /** ANSI color codes */
    private const COLOR_RESET  = "\033[0m";
    private const COLOR_GREEN  = "\033[32m";
    private const COLOR_RED    = "\033[31m";
    private const COLOR_YELLOW = "\033[33m";
    private const COLOR_CYAN   = "\033[36m";
    private const COLOR_BOLD   = "\033[1m";

    /** Whether ANSI colors are enabled */
    private bool $colorsEnabled;

    public function __construct(?bool $colorsEnabled = null)
    {
        $this->colorsEnabled = $colorsEnabled ?? $this->detectColors();
    }

    /**
     * Detect if the terminal supports colors.
     */
    private function detectColors() : bool
    {
        if (PHP_SAPI !== 'cli') {
            return false;
        }

        $term = getenv('TERM');

        return $term !== false && $term !== 'dumb'
            && function_exists('posix_isatty')
            && posix_isatty(STDOUT);
    }

    /**
     * Output an info message (green).
     */
    public function info(string $message) : void
    {
        $this->line($this->colorize($message, self::COLOR_GREEN));
    }

    /**
     * Output a plain line.
     */
    public function line(string $message = '') : void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Apply color if colors are enabled.
     */
    private function colorize(string $message, string $color) : string
    {
        if (! $this->colorsEnabled) {
            return $message;
        }

        return $color . $message . self::COLOR_RESET;
    }

    /**
     * Output an error message (red).
     */
    public function error(string $message) : void
    {
        $this->line($this->colorize($message, self::COLOR_RED));
    }

    /**
     * Output a warning message (yellow).
     */
    public function warn(string $message) : void
    {
        $this->line($this->colorize($message, self::COLOR_YELLOW));
    }

    /**
     * Output a comment message (cyan).
     */
    public function comment(string $message) : void
    {
        $this->line($this->colorize($message, self::COLOR_CYAN));
    }

    /**
     * Output a bold message.
     */
    public function bold(string $message) : void
    {
        $this->line($this->colorize($message, self::COLOR_BOLD));
    }

    /**
     * Render an ASCII table.
     */
    public function table(array $headers, array $rows) : void
    {
        Table::render($headers, $rows);
    }

    /**
     * Create and return a progress bar.
     */
    public function progress(int $total, int $width = 50) : ProgressBar
    {
        return new ProgressBar($total, $width);
    }

    /**
     * Ask a secret question.
     */
    public function askSecret(string $question, ?string $default = null) : string
    {
        return Question::askSecret($question, $default);
    }

    /**
     * Ask a yes/no confirmation.
     */
    public function confirm(string $question, bool $default = false) : bool
    {
        return Confirm::ask($question, $default);
    }

    /**
     * Ask a text question.
     */
    public function ask(string $question, ?string $default = null) : string
    {
        return Question::ask($question, $default);
    }

    /**
     * Write raw output without newline.
     */
    public function write(string $message) : void
    {
        echo $message;
    }

    /**
     * Output a new line.
     */
    public function newLine(int $count = 1) : void
    {
        echo str_repeat(PHP_EOL, $count);
    }
}
