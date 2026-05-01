<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\UI;

/**
 * Text input prompt.
 */
class Question
{
    /**
     * Ask a question and return the user's answer.
     */
    public static function ask(string $question, ?string $default = null): string
    {
        if ($default !== null) {
            echo sprintf('%s [%s]: ', $question, $default);
        } else {
            echo $question.': ';
        }

        $input = self::readLine();

        if ($input === '' && $default !== null) {
            return $default;
        }

        return $input;
    }

    /**
     * Read a line from stdin.
     */
    private static function readLine(): string
    {
        $handle = fopen('php://stdin', 'r');
        if ($handle === false) {
            return '';
        }

        $line = fgets($handle);
        fclose($handle);

        return $line === false ? '' : trim($line);
    }

    /**
     * Ask a secret question (hidden input).
     */
    public static function askSecret(string $question, ?string $default = null): string
    {
        if ($default !== null) {
            echo $question.' [hidden]: ';
        } else {
            echo $question.': ';
        }

        // Hide input for sensitive data
        if (PHP_SAPI === 'cli') {
            system('stty -echo');
        }

        $input = self::readLine();

        if (PHP_SAPI === 'cli') {
            system('stty echo');
        }

        echo PHP_EOL;

        if ($input === '' && $default !== null) {
            return $default;
        }

        return $input;
    }
}
