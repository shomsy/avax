<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\UI;

/**
 * Y/N confirmation prompt.
 */
class Confirm
{
    /**
     * Ask a yes/no question and return boolean.
     */
    public static function ask(string $question, bool $default = false): bool
    {
        $defaultStr = $default ? 'yes' : 'no';
        echo sprintf('%s (yes/no) [%s]: ', $question, $defaultStr);

        $input = self::readLine();

        if ($input === '') {
            return $default;
        }

        $normalized = strtolower(trim($input));

        return in_array($normalized, ['y', 'yes'], true);
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
}
