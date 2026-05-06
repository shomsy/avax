<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\UI;

/**
 * ASCII table output component.
 */
class Table
{
    /**
     * Render a table with headers and rows.
     */
    public static function render(array $headers, array $rows): void
    {
        if ($headers === []) {
            return;
        }

        $widths = self::calculateWidths($headers, $rows);

        self::renderSeparator($widths);
        self::renderRow($headers, $widths);
        self::renderSeparator($widths);

        foreach ($rows as $row) {
            self::renderRow($row, $widths);
        }

        self::renderSeparator($widths);
    }

    /**
     * Calculate column widths based on content.
     */
    private static function calculateWidths(array $headers, array $rows): array
    {
        $widths = array_map(mb_strlen(...), $headers);

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $cellStr = (string) $cell;
                $cellLen = mb_strlen($cellStr);
                $widths[$i] = max($widths[$i] ?? 0, $cellLen);
            }
        }

        return $widths;
    }

    /**
     * Render a separator line.
     */
    private static function renderSeparator(array $widths): void
    {
        $line = '+';

        foreach ($widths as $width) {
            $line .= str_repeat('-', $width + 2).'+';
        }

        echo $line.PHP_EOL;
    }

    /**
     * Render a single row.
     */
    private static function renderRow(array $row, array $widths): void
    {
        $line = '|';

        foreach ($row as $i => $cell) {
            $cellStr = (string) $cell;
            $width = $widths[$i] ?? 0;
            $line .= ' '.str_pad($cellStr, $width).' |';
        }

        echo $line.PHP_EOL;
    }
}
